@props(['city' => 'Magpet', 'lat' => 7.11, 'lon' => 125.10])

<div
    x-data="weatherCard({ lat: {{ $lat }}, lon: {{ $lon }} })"
    x-init="load()"
    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500">{{ $city }} weather</h3>
            <template x-if="data">
                <div class="mt-1 flex items-center gap-3">
                    <span class="text-5xl" x-text="info(data.current.weather_code)[0]"></span>
                    <div>
                        <div class="text-4xl font-semibold text-gray-900" x-text="t(data.current.temperature_2m) + '°'"></div>
                        <div class="text-sm text-gray-500" x-text="info(data.current.weather_code)[1]"></div>
                    </div>
                </div>
            </template>
        </div>

        <div class="text-right">
            <div class="inline-flex rounded-md border border-gray-200 text-sm overflow-hidden">
                <button type="button" @click="unit='C'" :class="unit==='C' ? 'bg-gray-800 text-white' : 'text-gray-600'" class="px-3 py-1">°C</button>
                <button type="button" @click="unit='F'" :class="unit==='F' ? 'bg-gray-800 text-white' : 'text-gray-600'" class="px-3 py-1">°F</button>
            </div>
            <template x-if="data">
                <dl class="mt-3 flex gap-5 text-sm text-gray-500">
                    <div><dd class="font-semibold text-gray-900" x-text="data.current.relative_humidity_2m + '%'"></dd><dt>Humidity</dt></div>
                    <div><dd class="font-semibold text-gray-900" x-text="Math.round(data.current.wind_speed_10m) + ' km/h'"></dd><dt>Wind</dt></div>
                </dl>
            </template>
        </div>
    </div>

    <p x-show="error" x-cloak class="mt-4 text-sm text-red-600">Weather is unavailable right now. Check your internet connection and reload the page.</p>
    <p x-show="!data && !error" class="mt-4 text-sm text-gray-500">Loading forecast…</p>

    <template x-if="data">
        <div>
            <div class="mt-5 rounded-md px-4 py-3 text-sm font-medium"
                 :class="maxRain >= 60 ? 'bg-amber-50 text-amber-800' : 'bg-green-50 text-green-800'"
                 x-text="maxRain >= 60
                    ? 'Heavy rain is likely in the next 12 hours. Plan pickups earlier and allow extra delivery time.'
                    : 'Mostly dry for the next 12 hours. Good conditions for pickups and deliveries.'"></div>

            <h4 class="mt-6 mb-2 text-sm font-semibold text-gray-700">Chance of rain, next 12 hours</h4>
            <div class="flex items-end gap-1 h-24">
                <template x-for="h in hours" :key="h.label">
                    <div class="flex-1 flex flex-col items-center justify-end h-full text-[11px] text-gray-500" :title="h.label + ': ' + h.p + '%'">
                        <span class="text-gray-700" x-text="h.p"></span>
                        <div class="w-full max-w-[26px] rounded-t bg-sky-500" :style="`height:${Math.max(h.p * 0.6, 2)}px`"></div>
                        <span x-text="h.label"></span>
                    </div>
                </template>
            </div>

            <h4 class="mt-6 mb-2 text-sm font-semibold text-gray-700">7-day forecast</h4>
            <div class="divide-y divide-gray-100 border-t border-gray-100">
                <template x-for="(d, i) in days" :key="d.date">
                    <div class="grid grid-cols-[3.5rem_2rem_2.5rem_1fr_2.5rem_3rem] items-center gap-2 py-2 text-sm">
                        <span class="font-medium text-gray-800" x-text="d.name"></span>
                        <span x-text="d.icon"></span>
                        <span class="text-right text-gray-500" x-text="t(d.lo) + '°'"></span>
                        <div class="relative h-1.5 rounded bg-gray-200">
                            <span class="absolute inset-y-0 rounded bg-gradient-to-r from-sky-400 to-amber-400"
                                  :style="`left:${(d.lo - min) / (max - min || 1) * 100}%;right:${100 - (d.hi - min) / (max - min || 1) * 100}%`"></span>
                        </div>
                        <span x-text="t(d.hi) + '°'"></span>
                        <span class="text-right text-sky-600" x-text="d.rain + '%'"></span>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

<script>
    function weatherCard({ lat, lon }) {
        return {
            data: null, error: false, unit: 'C', hours: [], days: [], maxRain: 0, min: 0, max: 1,

            t(c) { return this.unit === 'C' ? Math.round(c) : Math.round(c * 9 / 5 + 32); },

            info(code) {
                if (code === 0) return ['☀️', 'Clear sky'];
                if (code <= 2) return ['⛅', 'Partly cloudy'];
                if (code === 3) return ['☁️', 'Overcast'];
                if (code <= 48) return ['🌫️', 'Fog'];
                if (code <= 57) return ['🌦️', 'Drizzle'];
                if (code <= 67) return ['🌧️', 'Rain'];
                if (code <= 82) return ['🌦️', 'Rain showers'];
                return ['⛈️', 'Thunderstorm'];
            },

            async load() {
                const url = 'https://api.open-meteo.com/v1/forecast'
                    + `?latitude=${lat}&longitude=${lon}`
                    + '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m'
                    + '&hourly=precipitation_probability'
                    + '&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max'
                    + '&timezone=auto&forecast_days=7';
                try {
                    const d = await (await fetch(url)).json();
                    const start = Math.max(d.hourly.time.findIndex(x => x >= d.current.time), 0);
                    this.hours = d.hourly.precipitation_probability.slice(start, start + 12).map((p, k) => {
                        const h = new Date(d.hourly.time[start + k]).getHours();
                        return { p: p ?? 0, label: (h % 12 || 12) + (h < 12 ? 'a' : 'p') };
                    });
                    this.maxRain = Math.max(...this.hours.map(h => h.p));
                    this.days = d.daily.time.map((date, i) => ({
                        date,
                        name: i === 0 ? 'Today' : new Date(date + 'T00:00').toLocaleDateString('en', { weekday: 'short' }),
                        icon: this.info(d.daily.weather_code[i])[0],
                        lo: d.daily.temperature_2m_min[i],
                        hi: d.daily.temperature_2m_max[i],
                        rain: d.daily.precipitation_probability_max[i] ?? 0,
                    }));
                    this.min = Math.min(...this.days.map(x => x.lo));
                    this.max = Math.max(...this.days.map(x => x.hi));
                    this.data = d;
                } catch (e) { this.error = true; }
            },
        };
    }
</script>
@php
    /** @var \App\Models\Property|null $property */
    $property = $property ?? null;
@endphp

<div class="grid gap-5">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-stone-700">物件名 <span class="text-red-600">*</span></label>
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $property?->name) }}"
            required
            maxlength="255"
            class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700"
            placeholder="例: パークタワー青山 1203号室"
        >
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="price" class="mb-1 block text-sm font-medium text-stone-700">価格（万円） <span class="text-red-600">*</span></label>
            <input
                type="number"
                name="price"
                id="price"
                value="{{ old('price', $property?->price) }}"
                required
                min="1"
                max="999999"
                class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700"
                placeholder="6500"
            >
        </div>
        <div>
            <label for="station_minutes" class="mb-1 block text-sm font-medium text-stone-700">駅徒歩（分） <span class="text-red-600">*</span></label>
            <input
                type="number"
                name="station_minutes"
                id="station_minutes"
                value="{{ old('station_minutes', $property?->station_minutes) }}"
                required
                min="0"
                max="120"
                class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700"
                placeholder="8"
            >
        </div>
    </div>

    <div>
        <label for="address" class="mb-1 block text-sm font-medium text-stone-700">所在地 <span class="text-red-600">*</span></label>
        <input
            type="text"
            name="address"
            id="address"
            value="{{ old('address', $property?->address) }}"
            required
            maxlength="255"
            class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700"
            placeholder="東京都港区南青山1-2-3"
        >
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="latitude" class="mb-1 block text-sm font-medium text-stone-700">緯度</label>
            <input
                type="number"
                step="any"
                name="latitude"
                id="latitude"
                value="{{ old('latitude', $property?->latitude) }}"
                min="-90"
                max="90"
                class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700"
                placeholder="35.6656820"
            >
        </div>
        <div>
            <label for="longitude" class="mb-1 block text-sm font-medium text-stone-700">経度</label>
            <input
                type="number"
                step="any"
                name="longitude"
                id="longitude"
                value="{{ old('longitude', $property?->longitude) }}"
                min="-180"
                max="180"
                class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700"
                placeholder="139.7120880"
            >
        </div>
    </div>

    <div>
        <label for="evaluation" class="mb-1 block text-sm font-medium text-stone-700">評価</label>
        <select
            name="evaluation"
            id="evaluation"
            class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700"
        >
            <option value="">未設定</option>
            @foreach ($evaluations as $grade)
                <option value="{{ $grade }}" @selected(old('evaluation', $property?->evaluation) === $grade)>{{ $grade }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="memo" class="mb-1 block text-sm font-medium text-stone-700">メモ</label>
        <textarea
            name="memo"
            id="memo"
            rows="4"
            maxlength="5000"
            class="w-full border border-stone-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-700"
            placeholder="内見メモ、気になる点など"
        >{{ old('memo', $property?->memo) }}</textarea>
    </div>
</div>

@php
    /** @var \App\Models\Property|null $property */
    $property = $property ?? null;
@endphp

<div class="grid gap-5" data-postal-autofill>
    <div>
        <label for="name" class="mb-1 block text-sm font-bold text-ink">物件名 <span class="text-price">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $property?->name) }}" required maxlength="255" class="input-suumo" placeholder="例: パークタワー青山 1203号室">
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="price" class="mb-1 block text-sm font-bold text-ink">価格（万円） <span class="text-price">*</span></label>
            <input type="number" name="price" id="price" value="{{ old('price', $property?->price) }}" required min="1" max="999999" class="input-suumo" placeholder="6500">
        </div>
        <div>
            <label for="station_minutes" class="mb-1 block text-sm font-bold text-ink">駅徒歩（分） <span class="text-price">*</span></label>
            <input type="number" name="station_minutes" id="station_minutes" value="{{ old('station_minutes', $property?->station_minutes) }}" required min="0" max="120" class="input-suumo" placeholder="8">
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-[10rem_1fr]">
        <div>
            <label for="postal_code" class="mb-1 block text-sm font-bold text-ink">郵便番号</label>
            <input
                type="text"
                name="postal_code"
                id="postal_code"
                data-postal-code
                value="{{ old('postal_code', $property?->formattedPostalCode()) }}"
                maxlength="8"
                inputmode="numeric"
                autocomplete="postal-code"
                class="input-suumo"
                placeholder="100-0001"
            >
            <p class="mt-1 text-xs text-muted" data-postal-status></p>
        </div>
        <div>
            <label for="prefecture" class="mb-1 block text-sm font-bold text-ink">都道府県 <span class="text-price">*</span></label>
            <select name="prefecture" id="prefecture" data-prefecture required class="input-suumo">
                <option value="">選択してください</option>
                @foreach ($prefectures as $name)
                    <option value="{{ $name }}" @selected(old('prefecture', $property?->prefecture) === $name)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label for="address" class="mb-1 block text-sm font-bold text-ink">住所 <span class="text-price">*</span></label>
        <input
            type="text"
            name="address"
            id="address"
            data-address
            value="{{ old('address', $property?->address) }}"
            required
            maxlength="255"
            class="input-suumo"
            placeholder="港区南青山1-2-3"
        >
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="latitude" class="mb-1 block text-sm font-bold text-ink">緯度</label>
            <input type="number" step="any" name="latitude" id="latitude" value="{{ old('latitude', $property?->latitude) }}" min="-90" max="90" class="input-suumo" placeholder="35.6812360">
        </div>
        <div>
            <label for="longitude" class="mb-1 block text-sm font-bold text-ink">経度</label>
            <input type="number" step="any" name="longitude" id="longitude" value="{{ old('longitude', $property?->longitude) }}" min="-180" max="180" class="input-suumo" placeholder="139.7671250">
        </div>
    </div>

    @php
        $mapLat = old('latitude', $property?->latitude);
        $mapLng = old('longitude', $property?->longitude);
    @endphp
    <div>
        <p class="mb-2 text-sm font-bold text-ink">地図で座標を指定</p>
        <x-map
            :latitude="$mapLat"
            :longitude="$mapLng"
            :title="old('name', $property?->name) ?: '座標ピン'"
            :price="old('price', $property?->price)"
            :interactive="true"
            lat-input="latitude"
            lng-input="longitude"
            :zoom="filled($mapLat) && filled($mapLng) ? 15 : 13"
            height="320px"
        />
    </div>

    <div>
        <label for="evaluation" class="mb-1 block text-sm font-bold text-ink">評価</label>
        <select name="evaluation" id="evaluation" class="input-suumo">
            <option value="">未設定</option>
            @foreach ($evaluations as $grade)
                <option value="{{ $grade }}" @selected(old('evaluation', $property?->evaluation) === $grade)>{{ $grade }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="memo" class="mb-1 block text-sm font-bold text-ink">メモ</label>
        <textarea name="memo" id="memo" rows="4" maxlength="5000" class="input-suumo" placeholder="内見メモ、気になる点など">{{ old('memo', $property?->memo) }}</textarea>
    </div>
</div>

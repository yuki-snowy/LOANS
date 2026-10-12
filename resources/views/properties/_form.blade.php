@php
    /** @var \App\Models\Property|null $property */
    $property = $property ?? null;
@endphp

<div class="grid gap-5">
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

    <div>
        <label for="address" class="mb-1 block text-sm font-bold text-ink">所在地 <span class="text-price">*</span></label>
        <input type="text" name="address" id="address" value="{{ old('address', $property?->address) }}" required maxlength="255" class="input-suumo" placeholder="東京都港区南青山1-2-3">
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
        <p class="mb-3 text-xs text-muted">
            未入力時は東京駅周辺を初期表示。クリック／ドラッグで緯度・経度を設定（有料ジオコーディングAPIは未使用）。
        </p>
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

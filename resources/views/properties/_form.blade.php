@php
    /** @var \App\Models\Property|null $property */
    $property = $property ?? null;
@endphp

<div class="grid gap-5" data-postal-autofill data-property-form>
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
            <label for="station_minutes" class="mb-1 block text-sm font-bold text-ink">駅徒歩 <span class="text-price">*</span></label>
            <select name="station_minutes" id="station_minutes" required class="input-suumo">
                <option value="">選択してください</option>
                @foreach ($stationMinutes as $minutes)
                    <option value="{{ $minutes }}" @selected((string) old('station_minutes', $property?->station_minutes) === (string) $minutes)>
                        {{ $minutes }}分
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="floor_plan" class="mb-1 block text-sm font-bold text-ink">間取り <span class="text-price">*</span></label>
            <select name="floor_plan" id="floor_plan" required class="input-suumo">
                <option value="">選択してください</option>
                @foreach ($floorPlans as $plan)
                    <option value="{{ $plan }}" @selected(old('floor_plan', $property?->floor_plan) === $plan)>{{ $plan }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="built_year" class="mb-1 block text-sm font-bold text-ink">築年 <span class="text-price">*</span></label>
            <select name="built_year" id="built_year" required class="input-suumo">
                <option value="">選択してください</option>
                @foreach ($builtYears as $year)
                    <option value="{{ $year }}" @selected((string) old('built_year', $property?->built_year) === (string) $year)>
                        {{ $year }}年
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="exclusive_area" class="mb-1 block text-sm font-bold text-ink">専有面積（㎡） <span class="text-price">*</span></label>
            <input
                type="number"
                name="exclusive_area"
                id="exclusive_area"
                value="{{ old('exclusive_area', $property?->exclusive_area) }}"
                required
                min="1"
                max="9999.99"
                step="0.01"
                class="input-suumo"
                placeholder="65.40"
            >
        </div>
        <div>
            <label for="structure" class="mb-1 block text-sm font-bold text-ink">構造 <span class="text-price">*</span></label>
            <select name="structure" id="structure" required class="input-suumo">
                <option value="">選択してください</option>
                @foreach ($structures as $structure)
                    <option value="{{ $structure }}" @selected(old('structure', $property?->structure) === $structure)>{{ $structure }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label for="building_floors" class="mb-1 block text-sm font-bold text-ink">階建て <span class="text-price">*</span></label>
        <select name="building_floors" id="building_floors" required class="input-suumo">
            <option value="">選択してください</option>
            @foreach ($buildingFloors as $floors)
                <option value="{{ $floors }}" @selected((string) old('building_floors', $property?->building_floors) === (string) $floors)>
                    {{ $floors }}階建
                </option>
            @endforeach
        </select>
    </div>

    <div data-location-search class="border border-line bg-suumo-muted/40 p-3 sm:p-4">
        <label for="location_query" class="mb-1 block text-sm font-bold text-ink">郵便番号・住所から地図検索</label>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input
                type="text"
                id="location_query"
                data-location-query
                class="input-suumo"
                placeholder="例: 107-0062 または 東京都港区南青山"
                autocomplete="off"
            >
            <button type="button" data-location-submit class="btn-suumo shrink-0 px-4">地図で検索</button>
        </div>
        <p class="mt-1 text-xs text-muted" data-location-status>国土地理院の住所検索と郵便番号APIを利用します。</p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
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
            <input type="number" step="any" name="latitude" id="latitude" data-latitude value="{{ old('latitude', $property?->latitude) }}" min="-90" max="90" class="input-suumo" placeholder="35.6812360">
        </div>
        <div>
            <label for="longitude" class="mb-1 block text-sm font-bold text-ink">経度</label>
            <input type="number" step="any" name="longitude" id="longitude" data-longitude value="{{ old('longitude', $property?->longitude) }}" min="-180" max="180" class="input-suumo" placeholder="139.7671250">
        </div>
    </div>

    @php
        $mapLat = old('latitude', $property?->latitude);
        $mapLng = old('longitude', $property?->longitude);
    @endphp
    <div>
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-bold text-ink">地図で座標を指定（国土地理院）</p>
            <button type="button" data-reverse-geocode class="btn-suumo-outline text-xs">この位置の住所を取得</button>
        </div>
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

    <div data-image-upload>
        <label for="images" class="mb-1 block text-sm font-bold text-ink">画像（複数可）</label>
        <p class="mb-2 text-xs text-muted">最大10枚。選択時にブラウザ側で圧縮してから送信します。</p>
        <input
            type="file"
            name="images[]"
            id="images"
            data-image-input
            accept="image/jpeg,image/png,image/webp"
            multiple
            class="block w-full text-sm text-muted file:mr-3 file:border file:border-line file:bg-suumo-muted file:px-3 file:py-2 file:text-sm file:font-bold file:text-ink hover:file:bg-suumo-light"
        >
        <p class="mt-1 text-xs text-muted" data-image-status></p>
        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3" data-image-preview></div>

        @if ($property?->images?->isNotEmpty())
            <div class="mt-4">
                <p class="mb-2 text-sm font-bold text-ink">登録済み画像</p>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($property->images as $image)
                        <label class="relative block overflow-hidden border border-line bg-suumo-muted">
                            <img src="{{ $image->url() }}" alt="登録画像 {{ $loop->iteration }}" class="aspect-[4/3] h-28 w-full object-cover">
                            <span class="mt-1 flex items-center gap-2 px-2 py-1.5 text-xs font-bold text-ink">
                                <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="accent-[var(--color-suumo)]">
                                削除する
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
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

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" class="form-control" value="{{ old('sku', $sparepart->sku ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $sparepart->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Kategori</label>
        <select name="category_id" class="form-select">
            <option value="">-</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $sparepart->category_id ?? null) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Unit</label>
        <input type="text" name="unit" class="form-control" value="{{ old('unit', $sparepart->unit ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Harga Beli</label>
        <input type="number" step="0.01" name="price_buy" class="form-control" value="{{ old('price_buy', $sparepart->price_buy ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Harga Jual</label>
        <input type="number" step="0.01" name="price_sell" class="form-control" value="{{ old('price_sell', $sparepart->price_sell ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Minimum Stok</label>
        <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', $sparepart->min_stock ?? 0) }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $sparepart->description ?? '') }}</textarea>
    </div>
    <div class="col-12 mb-3 form-check">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked(old('is_active', $sparepart->is_active ?? true))>
        <label class="form-check-label">Aktif</label>
    </div>
</div>

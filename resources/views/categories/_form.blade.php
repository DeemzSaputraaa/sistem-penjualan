<div class="mb-3">
    <label class="form-label">Nama</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Kode</label>
    <input type="text" name="code" class="form-control" value="{{ old('code', $category->code ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label">Nama</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Telepon</label>
    <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Alamat</label>
    <textarea name="address" class="form-control" rows="3">{{ old('address', $customer->address ?? '') }}</textarea>
</div>

# Implementasi Vite untuk Dashboard SaaS

## Perubahan yang Dilakukan

### 1. Update Layout Files

#### File: `resources/views/layouts/includes/_base.blade.php`
- Mengganti `<link rel="stylesheet" href="{{asset('/build/css/app.css')}}" >` 
- Dengan `@vite(['resources/css/app.css'])`

#### File: `resources/views/layouts/includes/scripts.blade.php`  
- Mengganti `<script src="{{asset('/build/js/app.js')}}"></script>`
- Dengan `@vite(['resources/js/app.js'])`

### 2. Update Resources Files

#### File: `resources/css/app.css`
- Menambahkan Bootstrap import
- Menambahkan custom styles untuk dashboard
- Menyediakan utility classes

#### File: `resources/js/app.js`
- Tetap mengimport `./bootstrap.js`
- Bootstrap.js sudah mengimport lodash dan axios

### 3. Build Process

Konfigurasi Vite (`vite.config.js`) sudah correct:
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
```

## Cara Penggunaan

### Development Mode
```bash
npm run dev
```

### Production Build
```bash
npm run build
```

## Keuntungan Menggunakan @vite

1. **Auto Versioning**: File akan otomatis diberi hash untuk cache busting
2. **Hot Module Replacement**: Perubahan langsung terlihat saat development
3. **Asset Optimization**: Otomatis minifikasi dan optimasi
4. **Dynamic Path**: Path file akan otomatis disesuaikan dengan hash dari manifest.json

## Struktur File Build

Setelah build, file akan di-generate di:
- `public/build/assets/app-[hash].css`
- `public/build/assets/app-[hash].js`
- `public/build/manifest.json` (berisi mapping file)

Laravel akan otomatis membaca manifest.json dan menggunakan file dengan hash yang benar.

## Notes

- File lama masih ada di `public/build/css/` dan `public/build/js/` untuk kompatibilitas
- Directive `@vite` hanya bekerja setelah `npm run build` dijalankan
- Untuk development, gunakan `npm run dev` agar asset dapat hot reload
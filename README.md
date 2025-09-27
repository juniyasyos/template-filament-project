 # 🚀 Laravel Filament Template — Panel Starter

 Make beautiful admin panels fast. This project is a modern Laravel + Filament v4 starter with a sleek, customizable theme and sensible defaults — perfect as your next project template or a base to build internal tools.

 <p align="left">
   <a href="https://www.php.net/releases/8.2/en.php"><img alt="PHP" src="https://img.shields.io/badge/PHP-%5E8.2-777BB4?logo=php&logoColor=white"></a>
   <a href="https://laravel.com"><img alt="Laravel" src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white"></a>
   <a href="https://filamentphp.com"><img alt="Filament" src="https://img.shields.io/badge/Filament-4-00B5D8"></a>
   <a href="https://vuejs.org"><img alt="Vue" src="https://img.shields.io/badge/Vue-3-42B883?logo=vue.js&logoColor=white"></a>
   <a href="https://inertiajs.com"><img alt="Inertia" src="https://img.shields.io/badge/Inertia-v2-9553E9"></a>
   <a href="https://tailwindcss.com"><img alt="Tailwind" src="https://img.shields.io/badge/Tailwind-v4-38BDF8?logo=tailwindcss&logoColor=white"></a>
 </p>

 ---

 ## ✨ Highlights
 - 🧩 Filament v4 Admin ready out-of-the-box (panel at `/panel`).
 - 🎨 Modular theme with colors, mode (light/dark/system), and branding (logo + favicon).
 - ⚡ Modern stack: Laravel 12, PHP 8.2, Vite, Tailwind v4, Inertia + Vue 3.
 - 🧪 Testing with Pest pre-installed.
 - 🧭 Dev DX: single command to run server, queue, logs, and Vite.

 ---

 ## 🔧 Quick Start

 1) Clone & install
 ```bash
 composer install
 npm install
 cp .env.example .env
 php artisan key:generate
 ```

 2) Database (SQLite default)
 ```bash
 mkdir -p database && touch database/database.sqlite
 php artisan migrate
 ```

 3) Run all-in-one dev
 ```bash
 composer dev
 # This runs: php artisan serve, queue:listen, pail logs, and npm run dev
 ```

 4) Create an admin user for Filament
 ```bash
 php artisan make:filament-user
 ```

 5) Open the panel
 - http://localhost:8000/panel

 ---

 ## 🎨 Theming & Branding
 Konfigurasi tema dan panel dibuat modular.

 - Panel config: `config/panel.php`
   - id, path, name, version
   - theme.plugin (kelas plugin tema)
 - Theme config: `config/panel-theme.php`
   - colors, default_mode, brand (logo, favicon)
   - vite_path untuk entry CSS

 Plugin dan provider:
 - Plugin: `app/Filament/Plugins/PanelTheme.php`
 - Panel provider: `app/Providers/Filament/PanelPanelProvider.php`
 - CSS entry: `resources/css/filament/panel/theme.css` (sudah di `vite.config.ts`)

 ### Konfigurasi lewat .env
 ```env
 # Panel Settings
 PANEL_ID=panel
 PANEL_PATH=panel
 PANEL_NAME=Panel
 # Optional version label at topbar
 # PANEL_VERSION=1.0.0

 # Theme Colors & Mode
 PANEL_THEME_PRIMARY=#f59e0b
 PANEL_THEME_MODE=system  # system | light | dark

 # Branding (opsional)
 PANEL_BRAND_NAME=Panel
 # PANEL_BRAND_LOGO=/images/brand/logo-light.svg
 # PANEL_BRAND_LOGO_DARK=/images/brand/logo-dark.svg
 # PANEL_BRAND_LOGO_HEIGHT=1.5rem
 # PANEL_BRAND_FAVICON=/favicon.ico
 ```
 Simpan aset di `public/` (mis. `public/images/brand/...`).

 Setelah perubahan, clear config dan rebuild assets jika perlu:
 ```bash
 php artisan config:clear
 npm run dev # atau npm run build
 ```

 ### Catatan
 - Provider membaca `config('panel.*')` untuk `id`, `path`, `name`, `version`, dan `theme.plugin`.
 - Jika `PANEL_VERSION` diset, label versi tampil di topbar.

 ---

 ## 📦 Tech Stack
 - Laravel 12, PHP 8.2
 - Filament v4 (Panel at `/panel`)
 - Inertia + Vue 3
 - Vite + Tailwind v4 (`@tailwindcss/vite`)
 - Pest tests

 ---

 ## 🧰 NPM/Composer Scripts
 - `composer dev` — run server, queue, logs, and Vite together
 - `composer dev:ssr` — same but with Inertia SSR
 - `npm run dev` — Vite dev server
 - `npm run build` — production build
 - `composer test` — run tests

 ---

 ## 📁 Notable Paths
 - Panel provider: `app/Providers/Filament/PanelPanelProvider.php`
 - Theme plugin: `app/Filament/Plugins/PanelTheme.php`
 - Theme CSS: `resources/css/filament/panel/theme.css`
 - Panel config: `config/panel.php`
 - Theme config: `config/panel-theme.php`

 ---

 ## 🙌 Tips
 - Butuh palette lain? Gunakan `Filament\Support\Colors\Color::*` atau hex.
 - Ingin tampilan kustom? Ubah `resources/css/filament/panel/*` atau tambahkan partial baru.
 - Prefer MySQL/PostgreSQL? Update `.env` dan rerun migrations.

 ---

 ## 📝 License
 MIT — feel free to use, modify, and ship.

 ---

 ## 💬 Feedback
 Found something to improve or an idea to enhance the starter? Issues and PRs are welcome.

 Happy building! ✨

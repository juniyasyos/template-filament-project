 # 🚀 Laravel Filament Template — Siimut Starter

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
 - 🧩 Filament v4 Admin ready out-of-the-box (panel at `/siimut`).
 - 🎨 Theme plugin with color, dark/light/system mode, and branding (logo + favicon).
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
 - http://localhost:8000/siimut

 ---

 ## 🎨 Theming & Branding
 Theme is powered by a small plugin similar to resmatech/filament-awin-theme.

 - Plugin: `app/Filament/Plugins/SiimutTheme.php`
 - Panel registration: `app/Providers/Filament/SiimutPanelProvider.php`
 - CSS entry: `resources/css/filament/siimut/theme.css` (already in `vite.config.ts`)

 ### Configure via .env
 ```env
 # Colors
 SIIMUT_THEME_PRIMARY=#f59e0b
 SIIMUT_THEME_MODE=system  # system | light | dark

 # Branding (optional)
 SIIMUT_BRAND_NAME=Siimut
 SIIMUT_BRAND_LOGO=/images/brand/logo-light.svg
 SIIMUT_BRAND_LOGO_DARK=/images/brand/logo-dark.svg
 SIIMUT_BRAND_LOGO_HEIGHT=1.5rem
 SIIMUT_BRAND_FAVICON=/favicon.ico
 ```
 Assets under `public/` are recommended (e.g. `public/images/brand/...`).

 After changes, clear config and rebuild assets if needed:
 ```bash
 php artisan config:clear
 npm run dev # or npm run build
 ```

 ### Configure via code (optional)
 `app/Providers/Filament/SiimutPanelProvider.php`
 ```php
 use App\Filament\Plugins\SiimutTheme;
 use Filament\Support\Colors\Color;
 use Filament\Enums\ThemeMode;

 ->plugins([
     SiimutTheme::make()
         ->primaryColor(Color::Emerald)      // or ->primaryColor('#3b82f6')
         ->brandName('Siimut')
         ->brandLogo('/images/brand/logo-light.svg')
         ->darkModeBrandLogo('/images/brand/logo-dark.svg')
         ->brandLogoHeight('1.5rem')
         ->favicon('/favicon.ico')
         ->defaultMode(ThemeMode::System),
 ])
 ```

 ---

 ## 📦 Tech Stack
 - Laravel 12, PHP 8.2
 - Filament v4 (Panel at `/siimut`)
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
 - Panel provider: `app/Providers/Filament/SiimutPanelProvider.php`
 - Theme plugin: `app/Filament/Plugins/SiimutTheme.php`
 - Theme CSS: `resources/css/filament/siimut/theme.css`
 - Theme config: `config/siimut-theme.php`

 ---

 ## 🙌 Tips
 - Need a different primary palette? Use any `Filament\Support\Colors\Color::*`.
 - Want a custom look? Extend `resources/css/filament/siimut/*` or add new partials.
 - Prefer MySQL/PostgreSQL? Update `.env` and rerun migrations.

 ---

 ## 📝 License
 MIT — feel free to use, modify, and ship.

 ---

 ## 💬 Feedback
 Found something to improve or an idea to enhance the starter? Issues and PRs are welcome.

 Happy building! ✨


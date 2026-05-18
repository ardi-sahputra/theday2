<?php

// database/seeders/TemplateSeeder.php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Template;
use App\Models\TemplateCategory;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $pernikahan = TemplateCategory::where('slug', 'pernikahan')->firstOrFail();
        $storybook  = TemplateCategory::where('slug', 'storybook')->firstOrFail();
        $cinema     = TemplateCategory::where('slug', 'cinema')->firstOrFail();

        $weddingDemo = [
            'details' => [
                'groom_name'         => 'Ahmad Rizky',
                'bride_name'         => 'Siti Nurhaliza',
                'groom_photo_url'    => '/image/demo-image/groom.png',
                'bride_photo_url'    => '/image/demo-image/bride.png',
                'groom_parent_names' => 'Bpk. Hasan & Ibu Fatimah',
                'bride_parent_names' => 'Bpk. Rahmat & Ibu Aminah',
                'groom_instagram'    => 'ahmadrizky',
                'bride_instagram'    => 'sitinurhaliza',
                'opening_text'       => "Bismillahirrahmanirrahim\nDengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri pernikahan kami.",
                'closing_text'       => 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir untuk memberikan doa restu.',
            ],
            'events' => [
                [
                    'event_name'    => 'Akad Nikah',
                    'event_date'    => '2026-06-15',
                    'start_time'    => '08:00',
                    'end_time'      => '10:00',
                    'venue_name'    => 'Masjid Al-Ikhlas',
                    'venue_address' => 'Jl. Sudirman No. 123, Jakarta Selatan',
                    'maps_url'      => 'https://maps.google.com/?q=-6.2088,106.8456',
                ],
                [
                    'event_name'    => 'Resepsi',
                    'event_date'    => '2026-06-15',
                    'start_time'    => '11:00',
                    'end_time'      => '14:00',
                    'venue_name'    => 'Ballroom Hotel Mulia',
                    'venue_address' => 'Jl. Asia Afrika No. 8, Jakarta Selatan',
                    'maps_url'      => 'https://maps.google.com/?q=-6.2150,106.8070',
                ],
            ],
            'gallery' => [
                '/image/demo-image/galeri/galeri-0.webp',
                '/image/demo-image/galeri/galeri-1.webp',
                '/image/demo-image/galeri/galeri-0.webp',
                '/image/demo-image/galeri/galeri-1.webp',
            ],
            'gift' => [
                'accounts' => [
                    ['bank' => 'BCA',      'account_number' => '1234567890', 'account_name' => 'Ahmad Rizky'],
                    ['bank' => 'Mandiri',  'account_number' => '0987654321', 'account_name' => 'Siti Nurhaliza'],
                ],
            ],
            'love_story' => [
                [
                    'date'        => 'Maret 2020',
                    'title'       => 'Pertama Bertemu',
                    'description' => 'Kami pertama kali bertemu di sebuah seminar kampus. Satu tatapan yang tak terlupakan menjadi awal dari segalanya.',
                ],
                [
                    'date'        => 'Juni 2020',
                    'title'       => 'Jatuh Hati',
                    'description' => 'Setelah beberapa bulan sering menghabiskan waktu bersama, kami sadar ada sesuatu yang istimewa di antara kami.',
                ],
                [
                    'date'        => 'Desember 2021',
                    'title'       => 'Resmi Bersama',
                    'description' => 'Di bawah langit berbintang, Ahmad memberanikan diri mengungkapkan perasaannya. Siti pun menerima dengan senyum termanis.',
                ],
                [
                    'date'        => 'Juni 2026',
                    'title'       => 'Menuju Pelaminan',
                    'description' => 'Dengan restu kedua keluarga dan doa dari orang-orang tercinta, kami siap melangkah ke babak baru kehidupan bersama.',
                ],
            ],
        ];

        $templates = [
            // ── Pernikahan ─────────────────────────────────────────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Bunga Abadi',
                'slug'           => 'bunga-abadi',
                'thumbnail_url'  => null,
                'description'    => 'Template pernikahan elegan dengan sentuhan bunga dan nuansa hangat keemasan.',
                'default_config' => [
                    'primary_color'   => '#D4A373',
                    'secondary_color' => '#FEFAE0',
                    'accent_color'    => '#CCD5AE',
                    'font_title'      => 'Playfair Display',
                    'font_body'       => 'Poppins',
                    'layout'          => 'vertical-scroll',
                    'animation'       => 'fade-up',
                    'bg_pattern'      => 'floral',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'primary_color'   => '#D4A373',
                    'secondary_color' => '#FEFAE0',
                    'font_title'      => 'Playfair Display',
                    'font_body'       => 'Poppins',
                ]]),
                'tier'           => 'free',
                'is_active'      => true,
                'sort_order'     => 1,
            ],
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Langit Senja',
                'slug'           => 'langit-senja',
                'thumbnail_url'  => null,
                'description'    => 'Template pernikahan romantis dengan palet warna sunset yang memukau.',
                'default_config' => [
                    'primary_color'   => '#E57070',
                    'secondary_color' => '#FDE8E8',
                    'accent_color'    => '#F4A261',
                    'font_title'      => 'Cormorant Garamond',
                    'font_body'       => 'Lato',
                    'layout'          => 'vertical-scroll',
                    'animation'       => 'slide-up',
                    'bg_pattern'      => 'watercolor',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'primary_color'   => '#E57070',
                    'secondary_color' => '#FDE8E8',
                    'font_title'      => 'Cormorant Garamond',
                    'font_body'       => 'Lato',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 2,
            ],
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Hijau Daun',
                'slug'           => 'hijau-daun',
                'thumbnail_url'  => null,
                'description'    => 'Template pernikahan segar dengan nuansa alam dan dedaunan hijau.',
                'default_config' => [
                    'primary_color'   => '#52796F',
                    'secondary_color' => '#CAD2C5',
                    'accent_color'    => '#84A98C',
                    'font_title'      => 'DM Serif Display',
                    'font_body'       => 'Nunito',
                    'layout'          => 'vertical-scroll',
                    'animation'       => 'fade-in',
                    'bg_pattern'      => 'leaves',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'primary_color'   => '#52796F',
                    'secondary_color' => '#CAD2C5',
                    'font_title'      => 'DM Serif Display',
                    'font_body'       => 'Nunito',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 3,
            ],

            // ── Storybook (scene/illustrated templates) ────────────
            [
                'category_id'    => $storybook->id,
                'name'           => 'Beach',
                'slug'           => 'beach',
                'thumbnail_url'  => '/images/templates/beach/thumbnail.webp',
                'description'    => 'Template pernikahan interaktif bergaya pantai tropis — jelajahi setiap bagian undangan layaknya petualangan seru di tepi laut.',
                'default_config' => [],
                'demo_data'      => $weddingDemo,
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 5,
            ],
            [
                'category_id'    => $storybook->id,
                'name'           => 'Garden',
                'slug'           => 'garden',
                'thumbnail_url'  => '/images/templates/garden/thumbnail.webp',
                'description'    => 'Template pernikahan interaktif bergaya taman bunga yang hidup — temukan setiap detail undangan di antara hamparan bunga dan pepohonan yang rimbun.',
                'default_config' => [],
                'demo_data'      => $weddingDemo,
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 6,
            ],
            [
                'category_id'    => $storybook->id,
                'name'           => 'Night Sky',
                'slug'           => 'night-sky',
                'thumbnail_url'  => '/images/templates/night-sky/thumbnail.webp',
                'description'    => 'Template pernikahan interaktif bergaya langit malam berbintang — rayakan cinta di bawah ribuan bintang yang berkelip memukau.',
                'default_config' => [],
                'demo_data'      => $weddingDemo,
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 7,
            ],

            // ── Nusantara (Premium, dedicated renderer) ───────────
            [
                'category_id'    => $pernikahan->id,
                'name'           => 'Nusantara',
                'slug'           => 'nusantara',
                'thumbnail_url'  => null,
                'description'    => 'Template pernikahan premium bergaya keraton Jawa — megah, sakral, dan agung. Dilengkapi animasi gapura, ornamen batik kawung, sulur lung-lungan, dan mandala teratai.',
                'default_config' => [
                    'primary_color'       => '#8B6914',
                    'primary_color_light' => '#C9A84C',
                    'secondary_color'     => '#F5F0E8',
                    'accent_color'        => '#6B1D1D',
                    'dark_bg'             => '#2C1810',
                    'font_title'          => 'Cinzel Decorative',
                    'font_heading'        => 'Cormorant Garamond',
                    'font_body'           => 'Crimson Text',
                    'layout'              => 'vertical-scroll',
                    'animation'           => 'javanese-gate',
                    'has_opening_animation' => true,
                    'has_parallax'          => true,
                    'has_countdown'         => true,
                    'ornament_style'        => 'javanese',
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'primary_color'       => '#8B6914',
                    'primary_color_light' => '#C9A84C',
                    'secondary_color'     => '#F5F0E8',
                    'accent_color'        => '#6B1D1D',
                    'dark_bg'             => '#2C1810',
                    'font_title'          => 'Cinzel Decorative',
                    'font_heading'        => 'Cormorant Garamond',
                    'font_body'           => 'Crimson Text',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 4,
            ],

            // ── Cinema ────────────────────────────────────────────
            [
                'category_id'    => $cinema->id,
                'name'           => 'Netflix',
                'slug'           => 'netflix',
                'thumbnail_url'  => null,
                'description'    => 'Template undangan pernikahan bertema Netflix — cinematic, bold, dan ikonik. Tamu diajak masuk lewat layar "Who\'s Watching?" sebelum menikmati undangan seperti sebuah Netflix Original.',
                'default_config' => [
                    'netflix_subtitle' => 'Sebuah Kisah Cinta',
                    'netflix_tags'     => ['#lovestory', '#romantic', '#halal', '#forever'],
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'netflix_subtitle' => 'Sebuah Kisah Cinta',
                    'netflix_tags'     => ['#lovestory', '#romantic', '#halal', '#forever'],
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 8,
            ],

            // ── IG Stories (Premium Pop-Culture, tap-zone story deck) ──
            // Legal: brand-safe adaptation of the public vertical-ephemeral story-deck UX format.
            // NO Instagram logo, NO Helvetica / Instagram Sans, NO Meta wordmark. Default brand mark
            // rendered to users is "TheDay". See docs/superpowers/specs/premium-templates/ig-stories-design.md
            [
                'category_id'    => $cinema->id,
                'name'           => 'IG Stories',
                'slug'           => 'ig-stories',
                'thumbnail_url'  => '/images/templates/ig-stories/thumbnail.webp',
                'description'    => 'Undangan tap-zone story-deck format — 10 story full-screen vertical 9:16 dengan progress bars, auto-advance, swipe gesture, dan sticker interaktif (poll RSVP, question wishes, countdown). Untuk pasangan Gen-Z & millennial yang ingin undangan viral-shareable mobile-native.',
                'default_config' => [
                    'primary_color'        => '#833ab4',
                    'primary_color_light'  => '#fcb045',
                    'secondary_color'      => '#fd1d1d',
                    'accent_color'         => '#FFFFFF',
                    'dark_bg'              => '#000000',
                    'bg_color'             => '#000000',
                    'text_color'           => '#FFFFFF',
                    'text_secondary'       => 'rgba(255,255,255,0.72)',
                    'font_title'           => 'Inter',
                    'font_heading'         => 'Inter',
                    'font_body'            => 'Inter',
                    'gallery_layout'       => 'grid',
                    'opening_style'        => 'fade',
                    'section_backgrounds'  => [
                        'opening'    => ['type' => 'gradient', 'value' => 'sunset'],
                        'couple'     => ['type' => 'photo',    'value' => 'cover'],
                        'love_story' => ['type' => 'gradient', 'value' => 'pastel-pink'],
                        'events'     => ['type' => 'gradient', 'value' => 'blue-cyan'],
                        'countdown'  => ['type' => 'gradient', 'value' => 'red-urgent'],
                        'gallery'    => ['type' => 'collage',  'value' => 'grid'],
                        'rsvp'       => ['type' => 'gradient', 'value' => 'soft-poll'],
                        'gift'       => ['type' => 'gradient', 'value' => 'gold-swipe'],
                        'wishes'     => ['type' => 'gradient', 'value' => 'mint-question'],
                        'closing'    => ['type' => 'gradient', 'value' => 'sunset-cycle'],
                    ],
                    'ig_username'          => 'thedaywedding',
                    'ig_avatar_ring_style' => 'gradient',
                    'ig_story_duration'    => 6,
                    'ig_auto_advance'      => true,
                    'ig_story_order'       => ['opening','couple','love_story','events','countdown','gallery','rsvp','gift','wishes','closing'],
                    'ig_brand_name'        => 'TheDay',
                    'ig_show_overview'     => true,
                ],
                'demo_data'      => array_merge($weddingDemo, ['custom_config' => [
                    'ig_username'   => 'thedaywedding',
                    'ig_brand_name' => 'TheDay',
                ]]),
                'tier'           => 'premium',
                'is_active'      => true,
                'sort_order'     => 25,
            ],
        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(['slug' => $template['slug']], $template);
        }

        // Remove leftover birthday templates
        Template::whereIn('slug', ['confetti-ceria', 'biru-bintang', 'peach-balon'])->delete();
    }
}

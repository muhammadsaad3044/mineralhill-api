<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Dr. Tariq Jamil',
                'location_or_role' => 'Wellness Consultant, Lahore',
                'avatar_path' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&h=120&q=80',
                'rating' => 5,
                'product' => 'Gold Grade Shilajit Resin (50g)',
                'review' => 'The purity of Mineral Hill Shilajit is unmatched. Within two weeks of daily morning use dissolved in warm water, my vitality, mental focus, and sustained daily energy levels noticeably improved.',
                'reviewed_at' => '2026-11-21',
                'status' => 'published',
                'featured' => true,
            ],
            [
                'name' => 'Sarah Farooq',
                'location_or_role' => 'Yoga Instructor, Islamabad',
                'avatar_path' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=120&h=120&q=80',
                'rating' => 5,
                'product' => 'Pure Himalayan Shilajit Resin (30g)',
                'review' => 'Authentic Himalayan quality! The rich earthy aroma and resin texture dissolve cleanly. Helps significantly with post-workout muscle soreness and deep night recovery.',
                'reviewed_at' => '2026-11-20',
                'status' => 'published',
                'featured' => true,
            ],
            [
                'name' => 'Hamza Bilal',
                'location_or_role' => 'Marathon Runner, Karachi',
                'avatar_path' => null,
                'rating' => 4,
                'product' => 'Pure Shilajit Jumbo Pack (100g)',
                'review' => 'Great natural supplement for stamina. I take a pea-sized amount with green tea before training runs. Packaging was very secure and shipping was surprisingly fast.',
                'reviewed_at' => '2026-11-19',
                'status' => 'published',
                'featured' => false,
            ],
            [
                'name' => 'Zainab Qasim',
                'location_or_role' => 'Nutritionist, Rawalpindi',
                'avatar_path' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=120&h=120&q=80',
                'rating' => 5,
                'product' => 'Gold Grade Shilajit Resin (50g)',
                'review' => 'Laboratory-grade authentic fulvic acid content. I recommend this brand to my clients suffering from chronic fatigue and sluggish metabolism.',
                'reviewed_at' => '2026-11-18',
                'status' => 'published',
                'featured' => true,
            ],
            [
                'name' => 'Usman Ali Khan',
                'location_or_role' => 'CrossFit Athlete, Peshawar',
                'avatar_path' => null,
                'rating' => 3,
                'product' => 'Shilajit Starter Jar (15g)',
                'review' => 'Product quality is genuine, but the 15g jar finished faster than expected. Upgrading to the 50g jar on my next purchase for better longevity.',
                'reviewed_at' => '2026-11-17',
                'status' => 'published',
                'featured' => false,
            ],
            [
                'name' => 'Amina Sheikh',
                'location_or_role' => 'Corporate Executive, Lahore',
                'avatar_path' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=120&h=120&q=80',
                'rating' => 5,
                'product' => 'Pure Himalayan Shilajit Resin (30g)',
                'review' => 'Replaced my afternoon double espresso with warm water and Mineral Hill Shilajit. No caffeine jitters and no midday brain fog. Wonderful clean energy.',
                'reviewed_at' => '2026-11-16',
                'status' => 'pending',
                'featured' => false,
            ],
            [
                'name' => 'Major (R) Asad Raza',
                'location_or_role' => 'Senior Citizen, Abbottabad',
                'avatar_path' => null,
                'rating' => 5,
                'product' => 'Gold Grade Shilajit Resin (50g)',
                'review' => 'Remarkable joint relief and overall vigor. Reminds me of the raw Shilajit we used to source directly from Skardu valleys decades ago.',
                'reviewed_at' => '2026-11-15',
                'status' => 'pending',
                'featured' => false,
            ],
            [
                'name' => 'Marium Naveed',
                'location_or_role' => 'Verified Buyer, Multan',
                'avatar_path' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=120&h=120&q=80',
                'rating' => 2,
                'product' => 'Pure Himalayan Shilajit Resin (30g)',
                'review' => 'The earthy mineral taste was much stronger than expected. The seller explained it is completely natural, but it took time to get accustomed to.',
                'reviewed_at' => '2026-11-14',
                'status' => 'hidden',
                'featured' => false,
            ],
            [
                'name' => 'Fahad Mehmood',
                'location_or_role' => 'Software Architect, Islamabad',
                'avatar_path' => null,
                'rating' => 5,
                'product' => 'Pure Shilajit Jumbo Pack (100g)',
                'review' => 'Genuine test: dissolves 100% in warm water without any residue or bitter synthetic odor. Top shelf Himalayan gold grade.',
                'reviewed_at' => '2026-11-13',
                'status' => 'published',
                'featured' => true,
            ],
            [
                'name' => 'Dr. Nadia Parveen',
                'location_or_role' => 'Ayurvedic Specialist, Faisalabad',
                'avatar_path' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=120&h=120&q=80',
                'rating' => 4,
                'product' => 'Gold Grade Shilajit Resin (50g)',
                'review' => 'Excellent batch consistency and high mineral potency. Very pleased with the quality control and tamper-proof luxury packaging.',
                'reviewed_at' => '2026-11-12',
                'status' => 'published',
                'featured' => false,
            ],
        ];

        foreach ($testimonials as $item) {
            Testimonial::firstOrCreate(
                ['name' => $item['name'], 'review' => $item['review']],
                $item
            );
        }
    }
}

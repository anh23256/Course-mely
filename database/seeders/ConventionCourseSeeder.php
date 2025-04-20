<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConventionCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();

            $randomCourseIds = Course::where([
                'status' => 'approved',
                'visibility' => 'public'
            ])->where('price', '!=', 0)->get();

            foreach ($randomCourseIds as $course) {
                $conversation =   Conversation::create([
                    'name' => 'Nhóm thảo luận của khóa học ' . $course->name,
                    'owner_id' => $course->user_id,
                    'type' => 'group',
                    'status' => true,
                    'conversationable_id' => $course->id,
                    'conversationable_type' => Course::class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $conversation->users()->attach($course->user_id);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
        }
    }
}

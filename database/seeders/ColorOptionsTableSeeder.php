<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ColorOptionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('color_options')->delete();
        
        \DB::table('color_options')->insert(array (
            0 => 
            array (
                'id' => 1,
                'blue_bg' => '#0284a2',
                'red_bg' => '#f44a4a',
                'grey_bg' => '#f7f8fa',
                'light_grey_bg' => '#f9f9f9',
                'black_bg' => '#29303b',
                'white_bg' => '#ffffff',
                'dark_red_bg' => '#992337',
                'black_text' => '#29303b',
                'light_grey_text' => '#777777',
                'dark_grey_text' => '#686f7a',
                'red_text' => '#f44a4a',
                'blue_text' => '#0284a2',
                'dark_blue_text' => '#003845',
                'white_text' => '#ffffff',
                'linear_bg_one' => '#f44a4a',
                'linear_bg_two' => '#6e1a52',
                'linear_reverse_bg_one' => '#6e1a52',
                'linear_reverse_bg_two' => '#f44a4a',
                'linear_about_bg_one' => '#f44a4a',
                'linear_about_bg_two' => '#6e1a52',
                'linear_about_bluebg_one' => '#1a263a',
                'linear_about_bluebg_two' => '#4a8394',
                'linear_career_bg_one' => '#f5c252',
                'linear_career_bg_two' => '#6ac1d0',
                'created_at' => '2020-10-25 00:31:50',
                'updated_at' => '2020-10-30 12:36:33',
            ),
        ));
        
        
    }
}
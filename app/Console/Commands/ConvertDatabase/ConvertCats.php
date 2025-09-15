<?php

namespace App\Console\Commands\ConvertDatabase;



class ConvertCatsHelper
{
    private $cats = array("Action" => 23, "Adventure" => 26, "Avant Garde" => 77, "Award Winning" => 78, "Boys Love" => 59, "Comedy" => 24, "Drama" => 31, "Fantasy" => 45, "Girls Love" => 60, "Gourmet" => 93, "Horror" => 29, "Mystery" => 56, "Romance" => 40, "Sci-Fi" => 44, "Slice Of Life" => 38, "Sports" => 32, "Supernatural" => 54, "Suspense" => 79, "Ecchi" => 25, "Erotica" => 25, "Hentai" => 68, "Adult Cast" => 80, "Anthropomorphic" => 81, "CGDCT" => 82, "Childcare" => 82, "Combat Sports" => 32, "Crossdressing" => 83, "Delinquents" => 87, "Detective" => 30, "Educational" => 33, "Gore" => 92, "Harem" => 36, "High Stakes Game" => 48, "Historical" => 41, "Idols" => 42, "Isekai" => 76, "Iyashikei" => 38, "Love Polygon" => 94, "Magical Sex Shift" => 88, "Mahou Shoujo" => 27, "Martial Arts" => 53, "Mecha" => 28, "Medical" => 84, "Military" => 70, "Music" => 42, "Mythology" => 85, "Organized Crime" => 87, "Otaku Culture" => 89, "Parody" => 62, "Performing Arts" => 42, "Pets" => 90, "Psychological" => 57, "Racing" => 66, "Reincarnation" => 76, "Reverse Harem" => 36, "Romantic Subtext" => 40, "Samurai" => 72, "School" => 33, "Showbiz" => 42, "Space" => 52, "Strategy Game" => 48, "Super Power" => 51, "Survival" => 86, "Team Sports" => 32, "Time Travel" => 76, "Vampire" => 55, "Video Game" => 48, "Visual Arts" => 95, "Workplace" => 58, "Josei" => 61, "Kids" => 69, "Seinen" => 50, "Shoujo" => 49, "Shounen" => 35, "Gag Humor" => 91);

    /* `final_anime`.`anime_genres` */
    private $anime_genres = array(
        array('id' => '1', 'mal_id' => '1', 'name' => 'Action'),
        array('id' => '2', 'mal_id' => '2', 'name' => 'Adventure'),
        array('id' => '3', 'mal_id' => '5', 'name' => 'Avant Garde'),
        array('id' => '4', 'mal_id' => '46', 'name' => 'Award Winning'),
        array('id' => '5', 'mal_id' => '28', 'name' => 'Boys Love'),
        array('id' => '6', 'mal_id' => '4', 'name' => 'Comedy'),
        array('id' => '7', 'mal_id' => '8', 'name' => 'Drama'),
        array('id' => '8', 'mal_id' => '10', 'name' => 'Fantasy'),
        array('id' => '9', 'mal_id' => '26', 'name' => 'Girls Love'),
        array('id' => '10', 'mal_id' => '47', 'name' => 'Gourmet'),
        array('id' => '11', 'mal_id' => '14', 'name' => 'Horror'),
        array('id' => '12', 'mal_id' => '7', 'name' => 'Mystery'),
        array('id' => '13', 'mal_id' => '22', 'name' => 'Romance'),
        array('id' => '14', 'mal_id' => '24', 'name' => 'Sci-Fi'),
        array('id' => '15', 'mal_id' => '36', 'name' => 'Slice of Life'),
        array('id' => '16', 'mal_id' => '30', 'name' => 'Sports'),
        array('id' => '17', 'mal_id' => '37', 'name' => 'Supernatural'),
        array('id' => '18', 'mal_id' => '41', 'name' => 'Suspense'),
        array('id' => '19', 'mal_id' => '9', 'name' => 'Ecchi'),
        array('id' => '20', 'mal_id' => '49', 'name' => 'Erotica'),
        array('id' => '21', 'mal_id' => '12', 'name' => 'Hentai'),
        array('id' => '22', 'mal_id' => '50', 'name' => 'Adult Cast'),
        array('id' => '23', 'mal_id' => '51', 'name' => 'Anthropomorphic'),
        array('id' => '24', 'mal_id' => '52', 'name' => 'CGDCT'),
        array('id' => '25', 'mal_id' => '53', 'name' => 'Childcare'),
        array('id' => '26', 'mal_id' => '54', 'name' => 'Combat Sports'),
        array('id' => '27', 'mal_id' => '81', 'name' => 'Crossdressing'),
        array('id' => '28', 'mal_id' => '55', 'name' => 'Delinquents'),
        array('id' => '29', 'mal_id' => '39', 'name' => 'Detective'),
        array('id' => '30', 'mal_id' => '56', 'name' => 'Educational'),
        array('id' => '31', 'mal_id' => '57', 'name' => 'Gag Humor'),
        array('id' => '32', 'mal_id' => '58', 'name' => 'Gore'),
        array('id' => '33', 'mal_id' => '35', 'name' => 'Harem'),
        array('id' => '34', 'mal_id' => '59', 'name' => 'High Stakes Game'),
        array('id' => '35', 'mal_id' => '13', 'name' => 'Historical'),
        array('id' => '36', 'mal_id' => '60', 'name' => 'Idols (Female)'),
        array('id' => '37', 'mal_id' => '61', 'name' => 'Idols (Male)'),
        array('id' => '38', 'mal_id' => '62', 'name' => 'Isekai'),
        array('id' => '39', 'mal_id' => '63', 'name' => 'Iyashikei'),
        array('id' => '40', 'mal_id' => '64', 'name' => 'Love Polygon'),
        array('id' => '41', 'mal_id' => '65', 'name' => 'Magical Sex Shift'),
        array('id' => '42', 'mal_id' => '66', 'name' => 'Mahou Shoujo'),
        array('id' => '43', 'mal_id' => '17', 'name' => 'Martial Arts'),
        array('id' => '44', 'mal_id' => '18', 'name' => 'Mecha'),
        array('id' => '45', 'mal_id' => '67', 'name' => 'Medical'),
        array('id' => '46', 'mal_id' => '38', 'name' => 'Military'),
        array('id' => '47', 'mal_id' => '19', 'name' => 'Music'),
        array('id' => '48', 'mal_id' => '6', 'name' => 'Mythology'),
        array('id' => '49', 'mal_id' => '68', 'name' => 'Organized Crime'),
        array('id' => '50', 'mal_id' => '69', 'name' => 'Otaku Culture'),
        array('id' => '51', 'mal_id' => '20', 'name' => 'Parody'),
        array('id' => '52', 'mal_id' => '70', 'name' => 'Performing Arts'),
        array('id' => '53', 'mal_id' => '71', 'name' => 'Pets'),
        array('id' => '54', 'mal_id' => '40', 'name' => 'Psychological'),
        array('id' => '55', 'mal_id' => '3', 'name' => 'Racing'),
        array('id' => '56', 'mal_id' => '72', 'name' => 'Reincarnation'),
        array('id' => '57', 'mal_id' => '73', 'name' => 'Reverse Harem'),
        array('id' => '58', 'mal_id' => '74', 'name' => 'Love Status Quo'),
        array('id' => '59', 'mal_id' => '21', 'name' => 'Samurai'),
        array('id' => '60', 'mal_id' => '23', 'name' => 'School'),
        array('id' => '61', 'mal_id' => '75', 'name' => 'Showbiz'),
        array('id' => '62', 'mal_id' => '29', 'name' => 'Space'),
        array('id' => '63', 'mal_id' => '11', 'name' => 'Strategy Game'),
        array('id' => '64', 'mal_id' => '31', 'name' => 'Super Power'),
        array('id' => '65', 'mal_id' => '76', 'name' => 'Survival'),
        array('id' => '66', 'mal_id' => '77', 'name' => 'Team Sports'),
        array('id' => '67', 'mal_id' => '78', 'name' => 'Time Travel'),
        array('id' => '68', 'mal_id' => '32', 'name' => 'Vampire'),
        array('id' => '69', 'mal_id' => '79', 'name' => 'Video Game'),
        array('id' => '70', 'mal_id' => '80', 'name' => 'Visual Arts'),
        array('id' => '71', 'mal_id' => '48', 'name' => 'Workplace'),
        array('id' => '72', 'mal_id' => '82', 'name' => 'Urban Fantasy'),
        array('id' => '73', 'mal_id' => '83', 'name' => 'Villainess'),
        array('id' => '74', 'mal_id' => '43', 'name' => 'Josei'),
        array('id' => '75', 'mal_id' => '15', 'name' => 'Kids'),
        array('id' => '76', 'mal_id' => '42', 'name' => 'Seinen'),
        array('id' => '77', 'mal_id' => '25', 'name' => 'Shoujo'),
        array('id' => '78', 'mal_id' => '27', 'name' => 'Shounen'),
        array('id' => '79', 'mal_id' => '0', 'name' => 'Other')
    );

    public function getGenreIdsByCatIds(array $catIds): array
    {
        $genreIds = [];
        $cats_lower = array_change_key_case($this->cats, CASE_LOWER);

        foreach ($this->anime_genres as $genre) {
            $name_lower = strtolower($genre['name']);
            $cat_id = isset($cats_lower[$name_lower]) ? $cats_lower[$name_lower] : null;

            if ($cat_id && in_array($cat_id, $catIds)) {
                $genreIds[] = $genre['id'];
            }
        }

        return $genreIds;
    }
}

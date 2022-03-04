<?php

namespace App\Http\Controllers;

use App\Models\SearchProfile;
use App\Models\Property;
use JetBrains\PhpStorm\ArrayShape;

class MatchMakingController extends Controller
{
    /**
     * Get all search Profiles matching property fields.
     *
     * @param Property $property_id
     * @return \Illuminate\Http\Response
     */
    public function getSearchProfiles(Property $property_id): \Illuminate\Http\Response
    {
        $matchingSearchProfiles = [];
            $property_fields = $property_id->fields;
            //Get all search Profiles for a property type
            $search_profiles = SearchProfile::getPropertyTypeSearchProfiles($property_id->propertyType);
            foreach ($search_profiles as $sp){
                $search_fields = $sp->searchFields;
//                Get all fields that are common in the property profile as well as the search profile
                $fields_to_compare = array_intersect_key($property_fields, $search_fields);

                $profile_match_values = $this->checkSearchProfile($fields_to_compare, $search_fields);
                $looseMatchesCount = $profile_match_values["looseMatches"];
                $strictMatchesCount = $profile_match_values["strictMatches"];
                $missMatch = $profile_match_values["missMatch"];
                if($looseMatchesCount+$strictMatchesCount > 0 && !$missMatch){
                    $sp_array = $this->buildMatchingArray($looseMatchesCount,$strictMatchesCount,$sp->id);
                    array_push($matchingSearchProfiles, $sp_array);
                }
            }
            $sorted_profiles = $this->sortProfiles($matchingSearchProfiles);

            return response(["data"=>$sorted_profiles]);
    }


    /**
     * Build the matching array for a particular search profile
     * @param $loose
     * @param $strict
     * @param $sp_id
     * @return array in required format.
     */

    #[ArrayShape(["searchProfileId" => "", "score" => "", "strictMatchesCount" => "", "looseMatchesCount" => ""])]
    private function buildMatchingArray($loose, $strict, $sp_id): array
    {
        return [
            "searchProfileId" => $sp_id,
            "score" => $strict+$loose,
            "strictMatchesCount" => $strict,
            "looseMatchesCount" => $loose
        ];
    }

    #[ArrayShape(["missMatch" => "bool", "looseMatches" => "int", "strictMatches" => "int"])]
    private function checkSearchProfile($fields_to_compare, $search_fields): array
    {
        $strictMatchesCount = 0;
        $looseMatchesCount = 0;
        $missMatch = false;
        foreach($fields_to_compare as $key=>$value){
            if($value != NULL && $search_fields[$key] != NULL) {
                if(is_array($search_fields[$key])) {
                    $search_field_min_value = $search_fields[$key][0];
                    $search_field_max_value = $search_fields[$key][1];
                    $range_match = $this->checkRangeMatch($value, $search_field_min_value, $search_field_max_value);
                    if($range_match['strict']) {
                        $strictMatchesCount++;
                        continue;
                    }elseif ($range_match['loose']) {
                        $looseMatchesCount++;
                        continue;
                    }
                    //Check for Miss Matching
                    if (!$range_match['strict'] && !$range_match['loose'] && ($search_field_min_value != NULL || $search_field_max_value != NULL)) {
                        $missMatch = True;
                        break;
                    }
                }else{
                    if($value == $search_fields[$key]){
                        $strictMatchesCount++;
                        continue;
                    }else{
                        $missMatch = True;
                        break;
                    }
                }
            }
        }

        return [
            "missMatch" => $missMatch,
            "looseMatches" => $looseMatchesCount,
            "strictMatches" => $strictMatchesCount
        ];
    }

    #[ArrayShape(["strict" => "bool", "loose" => "bool"])]
    private function checkRangeMatch($value, $search_field_min_value, $search_field_max_value): array
    {
        $strict_match = FALSE;
        $loose_match = FALSE;
        if ($search_field_min_value == NULL && $search_field_max_value != NULL) {
            if ($value <= $search_field_max_value) {
                $strict_match = TRUE;
            } elseif ($value <= $this->maxDeviation($search_field_max_value, 25)) {
                $loose_match = TRUE;
            }
        } elseif ($search_field_min_value != NULL && $search_field_max_value == NULL) {
            if ($value >= $search_field_min_value) {
                $strict_match = TRUE;
            } elseif ($value >= $this->minDeviation($search_field_min_value, 25)) {
                $loose_match = TRUE;
            }
        } elseif ($search_field_min_value != NULL && $search_field_max_value != NULL) {
            if ($value <= $search_field_max_value && $value >= $search_field_min_value) {
                $strict_match = TRUE;
            } elseif ($value >= $this->minDeviation($search_field_min_value, 25) &&
                $value <= $this->maxDeviation($search_field_max_value, 25)) {
                $loose_match = TRUE;
            }
        }
        return [
            "strict" => $strict_match,
            "loose" =>$loose_match
        ];
    }

    private function minDeviation($val, $percent_dev): float|int
    {
       return $val - ($val * ($percent_dev/100));
    }

    private function maxDeviation($val, $percent_dev): float|int
    {
        return $val + ($val * ($percent_dev/100));
    }

    private function sortProfiles($matchingSearchProfiles){
        usort($matchingSearchProfiles, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        return $matchingSearchProfiles;
    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SearchProfile;
use App\Models\Property;

class MatchMakingController extends Controller
{
    /**
     * Get all search Profiles matching property fields.
     *
     * @param $property_id
     * @return \Illuminate\Http\Response
     */
    public function getSearchProfiles($property_id): \Illuminate\Http\Response
    {
        $matchingSearchProfiles = [];
        $property = Property::getProperty($property_id);
        if(!$property){
            return response("The property does not exist",'404');
        }else{
            $property_fields = $property->fields;
            //Get all search Profiles for a property type
            $search_profiles = SearchProfile::getPropertyTypeSearchProfiles($property->propertyType);
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
            $sorted_profiles = $this->sortMatchesScore($matchingSearchProfiles);

            return response(["data"=>$sorted_profiles]);
        }
    }


/**
 * Build the matching array for a particular search profile
 * @return array in required format.
 *
 */

    private function buildMatchingArray($loose, $strict, $sp_id){
        return [
            "searchProfileId" => $sp_id,
            "score" => $strict+$loose,
            "strictMatchesCount" => $strict,
            "looseMatchesCount" => $loose
        ];
    }

    private function checkSearchProfile($fields_to_compare, $search_fields){
        $strictMatchesCount = 0;
        $looseMatchesCount = 0;
        $missMatch = false;
        foreach($fields_to_compare as $key=>$value){
            if($value != NULL) {
                $search_field_min_value = $search_fields[$key][0];
                $search_field_max_value = $search_fields[$key][1];
//                        Check without and with 25% deviation
                if($search_field_min_value == NULL && $search_field_max_value !=NULL){
                    if($value <= $search_field_max_value){
                        $strictMatchesCount++;
                        continue;
                    }elseif($value <= ($search_field_max_value + ($search_field_max_value * 0.25))){
                        $looseMatchesCount++;
                        continue;
                    }
                }elseif($search_field_min_value != NULL && $search_field_max_value ==NULL){
                    if($value >= $search_field_min_value){
                        $strictMatchesCount++;
                        continue;
                    }elseif($value >= ($search_field_min_value - ($search_field_min_value * 0.25))){
                        $looseMatchesCount++;
                        continue;
                    }
                }elseif($search_field_min_value != NULL && $search_field_max_value !=NULL){
                    if($value <= $search_field_max_value && $value >= $search_field_min_value){
                        $strictMatchesCount++;
                        continue;
                    }elseif($value >= ($search_field_min_value - ($search_field_min_value * 0.25)) &&
                        $value <= ($search_field_max_value + ($search_field_max_value * 0.25))){
                        $looseMatchesCount++;
                        continue;
                    }
                }

                //Check for Miss Matching
                if($search_field_min_value != NULL || $search_field_max_value !=NULL){
                    $missMatch = True;
                    break;
                }
            }
        }

        return [
            "missMatch" => $missMatch,
            "looseMatches" => $looseMatchesCount,
            "strictMatches" => $strictMatchesCount
        ];
    }

    private function sortMatchesScore($matchingSearchProfiles){
        usort($matchingSearchProfiles, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $matchingSearchProfiles;
    }

}

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
     * @return \Illuminate\Http\Response
     */
    public function getSearchProfiles($property_id)
    {
        $matchingSearchProfiles = [];
        $property = Property::find($property_id);
//        if($property->isempty()){
        if(!$property){
            return response("The property does not exist",'404');
        }else{
            $property_fields = $property->fields;
            $search_profiles = SearchProfile::where('propertyType', 'LIKE', $property->propertyType)->get();
            foreach ($search_profiles as $sp){
                $strictMatchesCount = 0;
                $looseMatchesCount = 0;
                $missMatch = false;
                $search_fields = $sp->searchFields;
//                $keys = "";
                $fields_to_compare = array_intersect_key($property_fields, $search_fields);
                foreach($fields_to_compare as $key=>$value){
//                    Considering NULL to stand for a missing field
                    if($value != NULL) {
                        //Check for matching
                        $search_field_min_value = $search_fields[$key][0];
                        $search_field_max_value = $search_fields[$key][1];
//                        return response(['sp'=>$sp->id,"val"=>$value, "min" =>$search_field_min_value, 'max'=>$search_field_max_value]);
//                        Check without and with 25% deviation
                        if($search_field_min_value == NULL && $search_field_max_value !=NULL){
                            $search_field_min_value_dev = $search_field_min_value - ($search_field_min_value * 0.25);
                            if($value <= $search_field_max_value){
                                $strictMatchesCount++;
//                                $keys .="str1".$key; //Identify which search fields were strict or loose matches
                                continue;
                            }elseif($value <= ($search_field_max_value + ($search_field_max_value * 0.25))){
                                $looseMatchesCount++;
//                                $keys .="ls1".$key;
                                continue;
                            }
                        }elseif($search_field_min_value != NULL && $search_field_max_value ==NULL){
                            if($value >= $search_field_min_value){
                                $strictMatchesCount++;
//                                $keys .="str2".$key;
                                continue;
                            }elseif($value >= ($search_field_min_value - ($search_field_min_value * 0.25))){
                                $looseMatchesCount++;
//                                $keys .="ls2".$key;
                                continue;
                            }
                        }elseif($search_field_min_value != NULL && $search_field_max_value !=NULL){
                            if($value <= $search_field_max_value && $value >= $search_field_min_value){
                                $strictMatchesCount++;
//                                $keys .="str3".$key;
                                continue;
                            }elseif($value >= ($search_field_min_value - ($search_field_min_value * 0.25)) &&
                                $value <= ($search_field_max_value + ($search_field_max_value * 0.25))){
                                $looseMatchesCount++;
//                                $keys .="ls3".$key;
                                continue;
                            }
                        }

                        //Check for Miss Matching
                        if($search_field_min_value != NULL || $search_field_max_value !=NULL){
                            $missMatch = True;
//                            $keys .="mis".$key;
                            break;
                        }
                    }
                }
                if($looseMatchesCount+$strictMatchesCount > 0 && !$missMatch){
                    $sp_array = $this->buildMatchingArray($looseMatchesCount,$strictMatchesCount,$sp->id);
                    array_push($matchingSearchProfiles, $sp_array);
                }
            }
            usort($matchingSearchProfiles, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            return response(["data"=>$matchingSearchProfiles]);
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

}

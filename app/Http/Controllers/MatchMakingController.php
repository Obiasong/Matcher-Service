<?php

namespace App\Http\Controllers;

use App\Models\SearchProfile;
use App\Models\Property;
use Illuminate\Http\Response;
use JetBrains\PhpStorm\ArrayShape;

class MatchMakingController extends Controller
{
    /**
     * Get all search Profiles matching property fields.
     *
     * @param Property $propertyId
     * @return Response
     */
    public function getSearchProfiles(Property $propertyId): Response
    {
        $matchingProfiles = [];
            $propertyFields = $propertyId->fields;
            //Get all search Profiles for a property type
            $searchProfiles = SearchProfile::getPropertyTypeSearchProfiles($propertyId->propertyType);
            foreach ($searchProfiles as $spf){
                $searchFields = $spf->searchFields;
//                Get all fields that are common in the property profile as well as the search profile
                $intersectFields = array_intersect_key($propertyFields, $searchFields);

                $profileMatchValues = $this->checkSearchProfile($intersectFields, $searchFields);
                $looseMatchesCount = $profileMatchValues["looseMatches"];
                $strictMatchesCount = $profileMatchValues["strictMatches"];
                $missMatch = $profileMatchValues["missMatch"];
                if($looseMatchesCount+$strictMatchesCount > 0 && !$missMatch){
                    $spArray = $this->buildMatchingArray($looseMatchesCount,$strictMatchesCount,$spf->id);
                    array_push($matchingProfiles, $spArray);
                }
            }

            return response(["data"=>$this->sortProfiles($matchingProfiles)]);
    }


    /**
     * Build the matching array for a particular search profile
     * @param $loose
     * @param $strict
     * @param $spId
     * @return array in required format.
     */

    #[ArrayShape(["searchProfileId" => "", "score" => "", "strictMatchesCount" => "", "looseMatchesCount" => ""])]
    private function buildMatchingArray($loose, $strict, $spId): array
    {
        return [
            "searchProfileId" => $spId,
            "score" => $strict+$loose,
            "strictMatchesCount" => $strict,
            "looseMatchesCount" => $loose
        ];
    }

    #[ArrayShape(["missMatch" => "bool", "looseMatches" => "int", "strictMatches" => "int"])]
    private function checkSearchProfile($intersectFields, $searchFields): array
    {
        $strictMatchesCount = 0;
        $looseMatchesCount = 0;
        $missMatch = false;
        foreach($intersectFields as $key=> $value){
            if($value != NULL) {
                if(is_array($searchFields[$key])) {
                    $fieldMinValue = $searchFields[$key][0];
                    $fieldMaxValue = $searchFields[$key][1];
                    $rangeMatch = $this->checkRangeMatch($value, $fieldMinValue, $fieldMaxValue);
                    if($rangeMatch['strict']) {
                        $strictMatchesCount++;
                        continue;
                    }elseif ($rangeMatch['loose']) {
                        $looseMatchesCount++;
                        continue;
                    }
                    //Check for Miss Matching
                    if (!$rangeMatch['strict'] && !$rangeMatch['loose'] && ($fieldMinValue != NULL || $fieldMaxValue != NULL)) {
                        $missMatch = True;
                        break;
                    }
                }

                if($value == $searchFields[$key] || $searchFields[$key] == NULL){
                        $strictMatchesCount++;
                        continue;
                }else{
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

    #[ArrayShape(["strict" => "bool", "loose" => "bool"])]
    private function checkRangeMatch($value, $fieldMinValue, $fieldMaxValue): array
    {
        $strictMatch = FALSE;
        $looseMatch = FALSE;
        if ($fieldMinValue == NULL && $fieldMaxValue == NULL){
            $strictMatch = TRUE;
        } elseif ($fieldMinValue == NULL && $fieldMaxValue != NULL) {
            if ($value <= $fieldMaxValue)
                $strictMatch = TRUE;
            elseif ($value <= $this->maxDeviation($fieldMaxValue, 25))
                $looseMatch = TRUE;
        } elseif ($fieldMinValue != NULL && $fieldMaxValue == NULL) {
            if ($value >= $fieldMinValue)
                $strictMatch = TRUE;
            elseif ($value >= $this->minDeviation($fieldMinValue, 25))
                $looseMatch = TRUE;
        } elseif ($fieldMinValue != NULL && $fieldMaxValue != NULL) {
            if ($value <= $fieldMaxValue && $value >= $fieldMinValue)
                $strictMatch = TRUE;
            elseif ($value >= $this->minDeviation($fieldMinValue, 25) && $value <= $this->maxDeviation($fieldMaxValue, 25))
                $looseMatch = TRUE;
        }
        return [
            "strict" => $strictMatch,
            "loose" =>$looseMatch
        ];
    }

    private function minDeviation($val, $percentDev): float|int
    {
       return $val - ($val * ($percentDev/100));
    }

    private function maxDeviation($val, $percentDev): float|int
    {
        return $val + ($val * ($percentDev/100));
    }

    private function sortProfiles($searchProfiles){
        usort($searchProfiles, function($prev, $new) {
            return $new['score'] <=> $prev['score'];
        });
        return $searchProfiles;
    }

}

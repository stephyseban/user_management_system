<?php

namespace App\Traits;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserPartnerPreference;
use App\Models\UserFamilyInfo;
use App\Models\CastIntegration;
use App\Models\MaritalStatus;
use App\Models\EducationIntegration;
use App\Models\Age;
use Auth;

trait Common
{

    public function addressViewFeature($user_id)
    {

        $addressViewFeature = User::where('id', $user_id)->with('userPackage')->first();
        if ($addressViewFeature) {
            $data = [
                'address-view-feature' => $addressViewFeature->userPackage->address_view_feature,
                'address_view_count' => $addressViewFeature->userPackage->address_view_count,
                'daily_address_view_limit' => $addressViewFeature->userPackage->daily_address_view_limit,
                'interest_express_feature' => $addressViewFeature->userPackage->interest_express_feature,
                'interest_express_count' => $addressViewFeature->userPackage->interest_express_count,
            ];
            return $data;
        }
        return false;
    }

    public function coloumnList()
    {
        return [
            'users.id',
            'name',
            'register_id',
            'age',
            'gender',
            'height_list_id',
            'religions_id',
            'caste_id',
            'countries_id',
            'states_id',
            'districts_id',
            'locations_id',
            'education_parent_id',
            'education_category_id',
            'job_parent_id',
            'job_category_id',
            'register_date',
            'profile_verification_status',
            'job_score_id',
            'featured_profile_status',
            'register_package_id'
        ];
    }

    public function matchFilter($search_params)
    {
        $martialStatus = $search_params['marital_status'] ?? null;
        $heightFrom = $search_params['height_from'] ?? null;
        $heightTo = $search_params['height_to'] ?? null;
        $ageFrom = $search_params['age_from'] ?? null;
        $ageTo = $search_params['age_to'] ?? null;
        $filterReligion = $search_params['religion'] ?? null;
        $jathakamType = $search_params['jathakam_type'] ?? null;
        $education = $search_params['education'] ?? null;
        $job = $search_params['job'] ?? null;
        $location = $search_params['location'] ?? null;
        $fromDate = $search_params['from_date'] ?? null;
        $toDate = $search_params['to_date'] ?? null;
        $profileCreatedBy = $search_params['profile_created_by'] ?? null;
        $profileCreatedAt = $search_params['profile_created_at'] ?? null;
        $anyComplexionId = config('nest.settings.any_complex');
        $anyBodytypeId = config('nest.settings.any_body_type');

        $columns = $this->coloumnList();

        #fetching last 6 months
        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $endDate = now();
            $startDate = $endDate->copy()->subMonth($profileCreatedAt);
        }

        $userID = Auth::user()->id;

        $getUserPref = UserPartnerPreference::where('users_id', $userID)->first();
        if (isset($getUserPref)) {
            $preferedReligion = $getUserPref->religions_id;
            $preferedCaste = unserialize($getUserPref->caste_id);

            $preferAgeFromId = $getUserPref->prefer_age_from_id;
            $preferAgeFromValue = Age::select('age')->where('id', $preferAgeFromId)->first();
            $preferedAgeFrom = $preferAgeFromValue ? $preferAgeFromValue->age : null;

            $preferedAgeToId = $getUserPref->prefer_age_to_id;
            $preferAgeToValue = Age::select('age')->where('id', $preferedAgeToId)->first();
            $preferedAgeTo = $preferAgeToValue ? $preferAgeToValue->age : null;

            $preferedHeightFrom = $getUserPref->prefer_height_from_id;

            $preferedHeightTo = $getUserPref->prefer_height_to_id;

            $preferedMaritalStatus = unserialize($getUserPref->marital_status_id);

            $preferedCountry = $getUserPref->countries_id;

            $preferedState = unserialize($getUserPref->states_id);

            $preferedComplexion = unserialize($getUserPref->complexion_id);

            $preferedBodyType = unserialize($getUserPref->body_types_id);

            $preferedEducation = unserialize($getUserPref->education_category_id);

            $preferedJob = unserialize($getUserPref->job_category_id);
        }

        $basicInfo = User::find($userID);
        $familyInfo = UserFamilyInfo::where('users_id', $userID)->first();
        $gender = $basicInfo->gender;
        $religion = $basicInfo->religions_id;
        $caste = $basicInfo->caste_id;


        $integratedCaste = CastIntegration::select('match_caste')->where('caste_id', $caste)->first();

        if (isset($integratedCaste->match_caste)) {

            $intCaste = unserialize($integratedCaste->match_caste);
        } else {

            $intCaste = array();
        }

        #age filter
        $age = $basicInfo->age;
        $age_from = getMinAge($age, $gender);
        $age_to = getMaxAge($age, $gender);

        #height filter
        $height = $basicInfo->height_list_id;
        $height_from = getMinHeight($height, $gender);
        $height_to = getMaxHeight($height, $gender);

        #martial status filter
        if ($basicInfo->marital_status_id) {

            $marital_status = $basicInfo->marital_status_id;

            $mari_stat = config('nest.marital_status.never_married');

            $all_marital_status = MaritalStatus::select('id')->where('id', '!=', config('nest.marital_status.never_married'))->get();
        }

        #qualification filter
        if ($basicInfo->education_category_id) {

            $education_id = $basicInfo->education_category_id;

            $integratedEducation = EducationIntegration::select('match_education')->where('education_id', $education_id)->first();

            if (isset($integratedEducation)) {

                $intEdu = unserialize($integratedEducation->match_education);
            } else {

                $intEdu = array();
            }
        }

        $country = Auth::user()->userFamilyInfo->countries_id;
        $state = Auth::user()->userFamilyInfo->states_id;

        $whereConditions = [
            // ['profile_verification_status', config('nest.common_status.Active')],
            // ['profile_hide', config('nest.common_status.Inactive')],
            // ['profile_active_status', config('nest.common_status.Active')],
            ['profile_complete', config('nest.common_status.Active')],
            ['is_deleted', config('nest.common_status.Inactive')],
            ['users.profile_hide', config('nest.common_status.Inactive')],
            ['users.id', '!=', $userID],
        ];

        $matchList = User::with([
            'userFamilyInfo:id,users_id,countries_id,states_id,districts_id',
            'userFamilyInfo.userDistrict:district_name,id',
            'userFamilyInfo.userState:state_name,id',
            'userFamilyInfo.userCountry:country_name,id',
            'userReligion:religion_name,id', 'userHeightList:height,height_value,id', 'userCaste:caste_name,id', 'userEducationSubcategory:id,edu_category_title',
            'userJobSubCategory:subcategory_name,job_new_sub_categories.id,parent_child_job_new_mapping.job_category_id',
            'userImage' => function ($query) {
                $query->where('image_approve', config('nest.image.approved'));
                $query->orderBy('is_preference', 'desc');
            }
        ])
            ->where($whereConditions)
            ->whereDoesntHave('blockedUserList', function ($block) {
                $block->where('block_users.users_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            })
            ->whereDoesntHave('blockedByUserList', function ($block) {
                $block->where('block_users.block_profile_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            });

        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $matchList->whereBetween('users.created_at', [$startDate, $endDate]);
        }

        if ($profileCreatedBy) {
            $matchList->where('users.profile_created', config('nest.profile_created_by.' . $profileCreatedBy));
        }

        if ($gender) {
            $matchList->where('users.gender', '!=', $gender);
        }


        if ($preferedReligion) {
            $matchList->where('users.religions_id', $preferedReligion);
        } else {
            if ($religion) {
                $matchList->where('users.religions_id', $religion);
            }
        }

        if (is_array($preferedCaste) && !empty($preferedCaste)) {
            $matchList->whereIn('users.caste_id', $preferedCaste);
        } else if (is_array($intCaste) && !empty($intCaste)) {
            $matchList->whereIn('users.caste_id', $intCaste);
        } else {
            $matchList->where('users.caste_id', $caste);
        }

        if ($preferedAgeFrom) {
            $matchList->whereBetween('users.age', [$preferedAgeFrom, $preferedAgeTo]);
        } else if ($age) {
            $matchList->whereBetween('users.age', [$age_from, $age_to]);
        }

        if ($preferedHeightFrom) {
            $matchList->whereBetween('users.height_list_id', [$preferedHeightFrom, $preferedHeightTo]);
        } else if ($height) {
            $matchList->whereBetween('users.height_list_id', [$height_from, $height_to]);
        }

        if (is_array($preferedMaritalStatus) && !empty($preferedMaritalStatus)) {
            $matchList->whereIn('users.marital_status_id', $preferedMaritalStatus);
        } else if (isset($marital_status)) {

            if ($marital_status == $mari_stat) {
                $matchList->where('users.marital_status_id', $marital_status);
            } else {
                $matchList->whereIn('users.marital_status_id', $all_marital_status);
            }
        }

        if (is_array($preferedComplexion) && !empty($preferedComplexion) && !in_array($anyComplexionId, $preferedComplexion)) {
            $matchList->whereIn('users.complexion_id', $preferedComplexion);
        }

        if (is_array($preferedBodyType) && !empty($preferedBodyType) && !in_array($anyBodytypeId, $preferedBodyType)) {
            $matchList->whereIn('users.body_types_id', $preferedBodyType);
        }

        if (is_array($preferedEducation) && !empty($preferedEducation)) {
            $matchList->whereIn('users.education_category_id', $preferedEducation);
        }

        if (is_array($preferedJob) && !empty($preferedJob)) {
            $matchList->whereIn('users.job_score_id', $preferedJob)
                ->whereNotNull('users.job_score_id')
                ->whereNotNull('users.job_category_id')
                ->whereColumn('users.job_score_id', 'users.job_category_id');
        }

        $stateFiltered = false;
        if ($country) {
            $matchList->whereHas('userFamilyInfo', function ($q) use ($country, $state, &$stateFiltered) {
                $q->where('user_family_info.countries_id', $country);
                if ($state) {
                    $q->where('user_family_info.states_id', $state);
                    $stateFiltered = true;
                }
            });
        }

        if (!$stateFiltered && $state) {
            $matchList->whereHas('userFamilyInfo', function ($q) use ($state) {
                $q->where('user_family_info.states_id', $state);
            });
        }


        #filter list
        if ($martialStatus) {
            $matchList->whereIn('users.marital_status_id', $martialStatus);
        }

        $heightToFiltered = false;

        if ($heightFrom) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightFrom, $heightTo, &$heightToFiltered) {
                $q->where('height_list.height_value', '>=', $heightFrom);
                if ($heightTo) {
                    $q->where('height_list.height_value', '<=', $heightTo);
                    $heightToFiltered = true;
                }
            });
        }

        if (!$heightToFiltered && $heightTo) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightTo) {
                $q->where('height_list.height_value', '<=', $heightTo);
            });
        }

        if ($ageFrom) {
            $matchList->where('users.age', '>=', $ageFrom);
        }

        if ($ageTo) {
            $matchList->where('users.age', '<=', $ageTo);
        }

        if ($filterReligion) {
            $matchList->where('users.religions_id', $filterReligion);
        }

        if ($filterReligion && $filterReligion == config('nest.religion.Hindu') && !empty($jathakamType)) {
            $matchList->whereIn('user_religious_info.jathakam_types_id', $jathakamType);
        } elseif (!empty($jathakamType)) {
            $matchList->whereIn('user_religious_info.jathakam_types_id', $jathakamType);
        }

        if ($education) {
            $matchList->where(function ($query) use ($education) {
                $query->WhereIn('users.education_category_id', $education);
            });
        }

        if ($job) {
            $matchList->where(function ($query) use ($job) {
                $query->WhereIn('users.job_score_id', $job);
            });
        }


        if ($location) {
            $matchList->where('users.locations_id', $location);
        }

        if ($fromDate) {
            $matchList->where('users.register_date', '>', $fromDate);
        }

        if ($toDate) {
            $matchList->where('users.register_date', '<', $toDate);
        }

        return $matchList;
    }

    public function dailyMatchFilter($search_params)
    {
        $userID = Auth::user()->id;

        $getUserPref = UserPartnerPreference::where('users_id', $userID)->first();
        $preferedReligion = null;
        $preferedCaste = null;
        $preferedAgeFrom = null;
        $preferedAgeTo = null;
        $preferedHeightFrom = null;
        $preferedHeightTo = null;
        $preferedEducation = null;
        if (!empty($getUserPref)) {
            $preferedReligion = $getUserPref->religions_id;
            $preferedCaste = unserialize($getUserPref->caste_id);

            $preferAgeFromId = $getUserPref->prefer_age_from_id;
            $preferAgeFromValue = Age::select('age')->where('id', $preferAgeFromId)->first();
            $preferedAgeFrom = $preferAgeFromValue ? $preferAgeFromValue->age : null;

            $preferedAgeToId = $getUserPref->prefer_age_to_id;
            $preferAgeToValue = Age::select('age')->where('id', $preferedAgeToId)->first();
            $preferedAgeTo = $preferAgeToValue ? $preferAgeToValue->age : null;

            $preferedHeightFrom = $getUserPref->prefer_height_from_id;

            $preferedHeightTo = $getUserPref->prefer_height_to_id;

            $preferedEducation = unserialize($getUserPref->education_category_id);
        }

        $basicInfo = User::find($userID);
        $gender = $basicInfo->gender;
        $religion = $basicInfo->religions_id;
        $caste = $basicInfo->caste_id;

        $whereConditions = [
            // ['profile_verification_status', config('nest.common_status.Active')],
            // ['profile_hide', config('nest.common_status.Inactive')],
            // ['profile_active_status', config('nest.common_status.Active')],
            ['users.profile_complete', config('nest.common_status.Active')],
            ['users.is_deleted', config('nest.common_status.Inactive')],
            ['users.profile_hide', config('nest.common_status.Inactive')],
            ['users.id', '!=', $userID],
        ];

        $matchList = User::with([
            'userFamilyInfo:id,users_id,countries_id,states_id,districts_id',
            'userFamilyInfo.userDistrict:district_name,id',
            'userFamilyInfo.userState:state_name,id',
            'userFamilyInfo.userCountry:country_name,id',
            'userReligion:religion_name,id', 'userHeightList:height,height_value,id', 'userCaste:caste_name,id', 'userEducationSubcategory:id,edu_category_title',
            'userJobSubCategory:subcategory_name,job_new_sub_categories.id,parent_child_job_new_mapping.job_category_id',
            'userImage' => function ($query) {
                $query->where('image_approve', config('nest.image.approved'));
                $query->orderBy('is_preference', 'desc');
            },
            'familyIncomePhotoPassword' => function ($subquery) {
                $subquery->select('id', 'photo_is_protected', 'user_id');
            },
        ])
            ->where($whereConditions)
            ->whereDoesntHave('blockedUserList', function ($block) {
                $block->where('block_users.users_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            })
            ->whereDoesntHave('blockedByUserList', function ($block) {
                $block->where('block_users.block_profile_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            });

        if ($gender) {
            $matchList->where('users.gender', '!=', $gender);
        }

        if ($preferedReligion) {
            $matchList->where('users.religions_id', $preferedReligion);
            if (is_array($preferedCaste) && !empty($preferedCaste)) {
                $matchList->whereIn('users.caste_id', $preferedCaste);
            } else {
                // We will apply filter based on users caste only if users religion and preferred religion are same
                // Otherwise we may end up with no results
                if ($religion && $caste && $preferedReligion == $religion) {
                    $integratedCaste = CastIntegration::select('match_caste')->where('caste_id', $caste)->first();
                    if (isset($integratedCaste->match_caste)) {
                        $intCaste = unserialize($integratedCaste->match_caste);
                    } else {
                        $intCaste = array();
                    }
                    if (is_array($intCaste) && !empty($intCaste)) {
                        $matchList->whereIn('users.caste_id', $intCaste);
                    } else {
                        $matchList->where('users.caste_id', $caste);
                    }
                }
            }
        } elseif ($religion) {
            $matchList->where('users.religions_id', $religion);
            if ($caste) {
                $integratedCaste = CastIntegration::select('match_caste')->where('caste_id', $caste)->first();
                if (isset($integratedCaste->match_caste)) {
                    $intCaste = unserialize($integratedCaste->match_caste);
                } else {
                    $intCaste = array();
                }
                if (is_array($intCaste) && !empty($intCaste)) {
                    $matchList->whereIn('users.caste_id', $intCaste);
                } else {
                    $matchList->where('users.caste_id', $caste);
                }
            }
        }

        #age filter
        $age = $basicInfo->age;
        if ($preferedAgeFrom) {
            $matchList->whereBetween('users.age', [$preferedAgeFrom, $preferedAgeTo]);
        } elseif ($age) {
            $age_from = getMinAge($age, $gender);
            $age_to = getMaxAge($age, $gender);
            $matchList->whereBetween('users.age', [$age_from, $age_to]);
        }

        #height filter
        $height = $basicInfo->height_list_id;
        if ($preferedHeightFrom) {
            $matchList->whereBetween('users.height_list_id', [$preferedHeightFrom, $preferedHeightTo]);
        } elseif ($height) {
            $height_from = getMinHeight($height, $gender);
            $height_to = getMaxHeight($height, $gender);
            $matchList->whereBetween('users.height_list_id', [$height_from, $height_to]);
        }

        if (is_array($preferedEducation) && !empty($preferedEducation)) {
            $matchList->whereIn('users.education_category_id', $preferedEducation);
        } else {
            if ($basicInfo->education_category_id) {
                $education_id = $basicInfo->education_category_id;
                $integratedEducation = EducationIntegration::select('match_education')->where('education_id', $education_id)->first();

                if (!empty($integratedEducation)) {
                    $intEdu = unserialize($integratedEducation->match_education);
                } else {
                    $intEdu = array();
                }
                if (is_array($intEdu) && !empty($intEdu)) {
                    $matchList->whereIn('users.education_category_id', $intEdu);
                } else {
                    $matchList->Where('users.education_category_id', $education_id);
                }
            }
        }

        return $matchList;
    }

    public function allMatchFilter($search_params)
    {
        $martialStatus = $search_params['marital_status'] ?? null;
        $heightFrom = $search_params['height_from'] ?? null;
        $heightTo = $search_params['height_to'] ?? null;
        $ageFrom = $search_params['age_from'] ?? null;
        $ageTo = $search_params['age_to'] ?? null;
        $filterReligion = $search_params['religion'] ?? null;
        $filterCaste = $search_params['caste'] ?? null;
        $education = $search_params['education'] ?? null;
        $job = $search_params['job'] ?? null;
        $profileCreatedBy = $search_params['profile_created_by'] ?? null;
        $profileCreatedAt = $search_params['profile_created_at'] ?? null;
        $filterCountry = $search_params['country'] ?? null;
        $filterState = $search_params['state'] ?? null;
        $filterDistrict = $search_params['district'] ?? null;

        #fetching last 6 months
        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $endDate = now();
            $startDate = $endDate->copy()->subMonth($profileCreatedAt);
        }

        $userID = Auth::user()->id;


        $basicInfo = User::find($userID);
        $gender = $basicInfo->gender;
        $religion = $basicInfo->religions_id;
        $caste = $basicInfo->caste_id;

        $marital_status = $basicInfo->marital_status_id;
        $mari_stat = config('nest.marital_status.never_married');

        $integratedCaste = CastIntegration::select('match_caste')->where('caste_id', $caste)->first();

        if (isset($integratedCaste->match_caste)) {
            $intCaste = unserialize($integratedCaste->match_caste);
        } else {
            $intCaste = array();
        }

        #age filter
        $age = $basicInfo->age;
        $age_from = getMinAge($age, $gender);
        $age_to = getMaxAge($age, $gender);

        #height filter
        $height = $basicInfo->height_list_id;
        $height_from = getMinHeight($height, $gender);
        $height_to = getMaxHeight($height, $gender);

        $whereConditions = [
            // ['profile_verification_status', config('nest.common_status.Active')],
            // ['profile_hide', config('nest.common_status.Inactive')],
            // ['profile_active_status', config('nest.common_status.Active')],
            ['users.profile_complete', config('nest.common_status.Active')],
            ['users.is_deleted', config('nest.common_status.Inactive')],
            // ['profile_verification_status', config('nest.common_status.Active')],
            ['users.id', '!=', $userID],
        ];

        $matchList = User::with([
            'userFamilyInfo:id,users_id,countries_id,states_id,districts_id',
            'userFamilyInfo.userDistrict:district_name,id',
            'userFamilyInfo.userState:state_name,id',
            'userFamilyInfo.userCountry:country_name,id',
            'userReligion:religion_name,id', 'userHeightList:height,height_value,id', 'userCaste:caste_name,id', 'userEducationSubcategory:id,edu_category_title',
            'userJobSubCategory:subcategory_name,job_new_sub_categories.id,parent_child_job_new_mapping.job_category_id',
            'userImage' => function ($query) {
                $query->where('image_approve', config('nest.image.approved'));
                $query->orderBy('is_preference', 'desc');
            },
            'familyIncomePhotoPassword' => function ($subquery) {
                $subquery->select('id', 'photo_is_protected', 'user_id');
            },
        ])
            ->where($whereConditions)
            ->whereDoesntHave('blockedUserList', function ($block) {
                $block->where('block_users.users_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            })
            ->whereDoesntHave('blockedByUserList', function ($block) {
                $block->where('block_users.block_profile_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            });

        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $matchList->whereBetween('users.created_at', [$startDate, $endDate]);
        }

        if ($profileCreatedBy) {
            $matchList->where('users.profile_created', $profileCreatedBy);
        }

        if ($gender) {
            $matchList->where('users.gender', '!=', $gender);
        }

        if ($filterReligion) {
            $matchList->where('users.religions_id', $filterReligion);

            if ($filterCaste) {
                $matchList->whereIn('users.caste_id', $filterCaste);
            }
        } else {
            if ($religion) {
                $matchList->where('users.religions_id', $religion);
            }
            if (is_array($intCaste) && !empty($intCaste)) {
                $matchList->whereIn('users.caste_id', $intCaste);
            } else {
                $matchList->where('users.caste_id', $caste);
            }
        }

        $ageFiltered = false;
        if ($ageFrom) {
            $matchList->where('users.age', '>=', $ageFrom);
            $ageFiltered = true;
        }
        if ($ageTo) {
            $matchList->where('users.age', '<=', $ageTo);
            $ageFiltered = true;
        }
        if (!$ageFiltered) {
            if ($age) {
                $matchList->whereBetween('users.age', [$age_from, $age_to]);
            }
        }

        $heightToFiltered = false;
        $heighFiltered = false;
        if ($heightFrom) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightFrom, $heightTo, &$heightToFiltered) {
                $q->where('height_list.height_value', '>=', $heightFrom);
                if ($heightTo) {
                    $q->where('height_list.height_value', '<=', $heightTo);
                    $heightToFiltered = true;
                }
            });
            $heighFiltered = true;
        }

        if (!$heightToFiltered && $heightTo) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightTo) {
                $q->where('height_list.height_value', '<=', $heightTo);
            });
            $heighFiltered = true;
        }

        if (!$heighFiltered) {
            if ($height) {
                $matchList->whereBetween('users.height_list_id', [$height_from, $height_to]);
            }
        }

        if ($martialStatus) {
            $matchList->whereIn('users.marital_status_id', $martialStatus);
        } else {
            if (!empty($marital_status)) {
                if ($marital_status == $mari_stat) {
                    $matchList->where('users.marital_status_id', $marital_status);
                } else {
                    $matchList->where('users.marital_status_id', '!=', $mari_stat);
                }
            }
        }

        if ($education) {
            $matchList->where(function ($query) use ($education) {
                $query->WhereIn('users.education_category_id', $education);
            });
        }

        if ($job) {
            $matchList->where(function ($query) use ($job) {
                $query->WhereIn('users.job_score_id', $job);
            });
        }

        if ($filterCountry) {
            $matchList->whereHas('userFamilyInfo', function ($q) use ($filterCountry, $filterState, &$filterDistrict) {
                $q->where('user_family_info.countries_id', $filterCountry);
                if ($filterState) {
                    $q->where('user_family_info.states_id', $filterState);
                    if ($filterDistrict) {
                        $q->whereIn('user_family_info.districts_id', $filterDistrict);
                    }
                }
            });
        }

        return $matchList;
    }

    public function topMatchFilter($search_params)
    {
        $martialStatus = $search_params['marital_status'] ?? null;
        $heightFrom = $search_params['height_from'] ?? null;
        $heightTo = $search_params['height_to'] ?? null;
        $ageFrom = $search_params['age_from'] ?? null;
        $ageTo = $search_params['age_to'] ?? null;
        $filterReligion = $search_params['religion'] ?? null;
        $filterCaste = $search_params['caste'] ?? null;
        $education = $search_params['education'] ?? null;
        $job = $search_params['job'] ?? null;
        $profileCreatedBy = $search_params['profile_created_by'] ?? null;
        $profileCreatedAt = $search_params['profile_created_at'] ?? null;
        $filterCountry = $search_params['country'] ?? null;
        $filterState = $search_params['state'] ?? null;
        $filterDistrict = $search_params['district'] ?? null;

        #fetching last 6 months
        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $endDate = now();
            $startDate = $endDate->copy()->subMonth($profileCreatedAt);
        }

        $userID = Auth::user()->id;

        $basicInfo = User::find($userID);
        $gender = $basicInfo->gender;
        $religion = $basicInfo->religions_id;
        $caste = $basicInfo->caste_id;

        $integratedCaste = CastIntegration::select('match_caste')->where('caste_id', $caste)->first();
        if (isset($integratedCaste->match_caste)) {
            $intCaste = unserialize($integratedCaste->match_caste);
        } else {
            $intCaste = array();
        }

        #age filter
        $age = $basicInfo->age;
        $age_from = getMinAge($age, $gender);
        $age_to = getMaxAge($age, $gender);

        #height filter
        $height = $basicInfo->height_list_id;
        $height_from = getMinHeight($height, $gender);
        $height_to = getMaxHeight($height, $gender);

        #martial status filter
        $marital_status = $basicInfo->marital_status_id;
        $mari_stat = config('nest.marital_status.never_married');

        #qualification filter
        $intEdu = array();
        $education_id = null;
        if ($basicInfo->education_category_id) {
            $education_id = $basicInfo->education_category_id;
            $integratedEducation = EducationIntegration::select('match_education')->where('education_id', $education_id)->first();
            if (isset($integratedEducation)) {
                $intEdu = unserialize($integratedEducation->match_education);
            } else {
                $intEdu = array();
            }
        }

        $country = Auth::user()->userFamilyInfo->countries_id;
        $state = Auth::user()->userFamilyInfo->states_id;

        $whereConditions = [
            // ['profile_verification_status', config('nest.common_status.Active')],
            // ['profile_hide', config('nest.common_status.Inactive')],
            // ['profile_active_status', config('nest.common_status.Active')],
            ['users.profile_complete', config('nest.common_status.Active')],
            ['users.is_deleted', config('nest.common_status.Inactive')],
            ['users.profile_hide', config('nest.common_status.Inactive')],
            // ['profile_verification_status', config('nest.common_status.Active')],
            ['users.id', '!=', $userID],
        ];

        $matchList = User::with([
            'userFamilyInfo:id,users_id,countries_id,states_id,districts_id',
            'userFamilyInfo.userDistrict:district_name,id',
            'userFamilyInfo.userState:state_name,id',
            'userFamilyInfo.userCountry:country_name,id',
            'userReligion:religion_name,id', 'userHeightList:height,height_value,id', 'userCaste:caste_name,id', 'userEducationSubcategory:id,edu_category_title',
            'userJobSubCategory:subcategory_name,job_new_sub_categories.id,parent_child_job_new_mapping.job_category_id',
            'userImage' => function ($query) {
                $query->where('image_approve', config('nest.image.approved'));
                $query->orderBy('is_preference', 'desc');
            }, 'familyIncomePhotoPassword' => function ($subquery) {
                $subquery->select('id', 'photo_is_protected', 'user_id');
            },
        ])
            ->where($whereConditions)
            ->whereDoesntHave('blockedUserList', function ($block) {
                $block->where('block_users.users_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            })
            ->whereDoesntHave('blockedByUserList', function ($block) {
                $block->where('block_users.block_profile_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            });

        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $matchList->whereBetween('users.created_at', [$startDate, $endDate]);
        }

        if ($profileCreatedBy) {
            $matchList->where('users.profile_created', $profileCreatedBy);
        }

        if ($gender) {
            $matchList->where('users.gender', '!=', $gender);
        }

        if ($filterReligion) {
            $matchList->where('users.religions_id', $filterReligion);

            if ($filterCaste) {
                $matchList->whereIn('users.caste_id', $filterCaste);
            }
        } else {
            if ($religion) {
                $matchList->where('users.religions_id', $religion);
            }
            if (is_array($intCaste) && !empty($intCaste)) {
                $matchList->whereIn('users.caste_id', $intCaste);
            } else {
                $matchList->where('users.caste_id', $caste);
            }
        }

        $ageFiltered = false;
        if ($ageFrom) {
            $matchList->where('users.age', '>=', $ageFrom);
            $ageFiltered = true;
        }
        if ($ageTo) {
            $matchList->where('users.age', '<=', $ageTo);
            $ageFiltered = true;
        }
        if (!$ageFiltered) {
            $matchList->whereBetween('users.age', [$age_from, $age_to]);
        }

        if ($martialStatus) {
            $matchList->whereIn('users.marital_status_id', $martialStatus);
        } else {
            if ($marital_status) {
                if ($marital_status == $mari_stat) {
                    $matchList->where('users.marital_status_id', $marital_status);
                } else {
                    $matchList->where('users.marital_status_id', '!=', $mari_stat);
                }
            }
        }

        $heightToFiltered = false;
        $heightFiltered = false;
        if ($heightFrom) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightFrom, $heightTo, &$heightToFiltered) {
                $q->where('height_list.height_value', '>=', $heightFrom);
                if ($heightTo) {
                    $q->where('height_list.height_value', '<=', $heightTo);
                    $heightToFiltered = true;
                }
            });
            $heightFiltered = true;
        }
        if (!$heightToFiltered && $heightTo) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightTo) {
                $q->where('height_list.height_value', '<=', $heightTo);
            });
            $heightFiltered = true;
        }
        if (!$heightFiltered) {
            if ($height) {
                $matchList->whereBetween('users.height_list_id', [$height_from, $height_to]);
            }
        }

        if ($education) {
            $matchList->where(function ($query) use ($education) {
                $query->WhereIn('users.education_category_id', $education);
            });
        } else {
            if (is_array($intEdu) && !empty($intEdu)) {
                $matchList->whereIn('users.education_category_id', $intEdu);
            } else {
                if ($education_id) {
                    $matchList->where('users.education_category_id', $education_id);
                }
            }
        }

        if ($job) {
            $matchList->where(function ($query) use ($job) {
                $query->WhereIn('users.job_score_id', $job);
            });
        }

        if ($filterCountry) {
            $matchList->whereHas('userFamilyInfo', function ($q) use ($filterCountry, $filterState, &$filterDistrict) {
                $q->where('user_family_info.countries_id', $filterCountry);
                if ($filterState) {
                    $q->where('user_family_info.states_id', $filterState);
                    if ($filterDistrict) {
                        $q->whereIn('user_family_info.districts_id', $filterDistrict);
                    }
                }
            });
        } else {
            $stateFiltered = false;
            if ($country) {
                $matchList->whereHas('userFamilyInfo', function ($q) use ($country, $state, &$stateFiltered) {
                    $q->where('user_family_info.countries_id', $country);
                    if ($state) {
                        $q->where('user_family_info.states_id', $state);
                        $stateFiltered = true;
                    }
                });
            }
            if (!$stateFiltered && $state) {
                $matchList->whereHas('userFamilyInfo', function ($q) use ($state) {
                    $q->where('user_family_info.states_id', $state);
                });
            }
        }

        return $matchList;
    }

    public function newMatchFilter($search_params)
    {
        $martialStatus = $search_params['marital_status'] ?? null;
        $heightFrom = $search_params['height_from'] ?? null;
        $heightTo = $search_params['height_to'] ?? null;
        $ageFrom = $search_params['age_from'] ?? null;
        $ageTo = $search_params['age_to'] ?? null;
        $filterReligion = $search_params['religion'] ?? null;
        $filterCaste = $search_params['caste'] ?? null;
        $education = $search_params['education'] ?? null;
        $job = $search_params['job'] ?? null;
        $profileCreatedBy = $search_params['profile_created_by'] ?? null;
        $profileCreatedAt = $search_params['profile_created_at'] ?? null;
        $anyComplexionId = config('nest.settings.any_complex');
        $anyBodytypeId = config('nest.settings.any_body_type');
        $filterCountry = $search_params['country'] ?? null;
        $filterState = $search_params['state'] ?? null;
        $filterDistrict = $search_params['district'] ?? null;

        #fetching last 6 months
        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $endDate = now();
            $startDate = $endDate->copy()->subMonth($profileCreatedAt);
        }

        $userID = Auth::user()->id;

        $getUserPref = UserPartnerPreference::where('users_id', $userID)->first();
        if (isset($getUserPref)) {
            $preferedReligion = $getUserPref->religions_id;
            $preferedCaste = unserialize($getUserPref->caste_id);

            $preferAgeFromId = $getUserPref->prefer_age_from_id;
            $preferAgeFromValue = Age::select('age')->where('id', $preferAgeFromId)->first();
            $preferedAgeFrom = $preferAgeFromValue ? $preferAgeFromValue->age : null;

            $preferedAgeToId = $getUserPref->prefer_age_to_id;
            $preferAgeToValue = Age::select('age')->where('id', $preferedAgeToId)->first();
            $preferedAgeTo = $preferAgeToValue ? $preferAgeToValue->age : null;

            $preferedHeightFrom = $getUserPref->prefer_height_from_id;

            $preferedHeightTo = $getUserPref->prefer_height_to_id;

            $preferedMaritalStatus = unserialize($getUserPref->marital_status_id);

            $preferedCountry = $getUserPref->countries_id;

            $preferedState = unserialize($getUserPref->states_id);

            $preferedComplexion = unserialize($getUserPref->complexion_id);

            $preferedBodyType = unserialize($getUserPref->body_types_id);

            $preferedEducation = unserialize($getUserPref->education_category_id);

            $preferedJob = unserialize($getUserPref->job_category_id);

            $preferedJathakamType = unserialize($getUserPref->prefer_jathakam_type);

            $preferedSpecialCase = unserialize($getUserPref->prefer_special_case);
        }

        $basicInfo = User::find($userID);
        $gender = $basicInfo->gender;
        $religion = $basicInfo->religions_id;
        $caste = $basicInfo->caste_id;

        $integratedCaste = CastIntegration::select('match_caste')->where('caste_id', $caste)->first();

        if (isset($integratedCaste->match_caste)) {
            $intCaste = unserialize($integratedCaste->match_caste);
        } else {
            $intCaste = array();
        }

        #age filter
        $age = $basicInfo->age;
        $age_from = getMinAge($age, $gender);
        $age_to = getMaxAge($age, $gender);

        #height filter
        $height = $basicInfo->height_list_id;
        $height_from = getMinHeight($height, $gender);
        $height_to = getMaxHeight($height, $gender);

        $marital_status = $basicInfo->marital_status_id;
        $mari_stat = config('nest.marital_status.never_married');

        #qualification filter
        $intEdu = array();
        $education_id = null;
        if ($basicInfo->education_category_id) {
            $education_id = $basicInfo->education_category_id;
            $integratedEducation = EducationIntegration::select('match_education')->where('education_id', $education_id)->first();
            if (isset($integratedEducation)) {
                $intEdu = unserialize($integratedEducation->match_education);
            } else {
                $intEdu = array();
            }
        }

        $country = Auth::user()->userFamilyInfo->countries_id;
        $state = Auth::user()->userFamilyInfo->states_id;

        $whereConditions = [
            // ['profile_verification_status', config('nest.common_status.Active')],
            // ['profile_hide', config('nest.common_status.Inactive')],
            // ['profile_active_status', config('nest.common_status.Active')],
            ['users.profile_complete', config('nest.common_status.Active')],
            ['users.is_deleted', config('nest.common_status.Inactive')],
            ['users.profile_hide', config('nest.common_status.Inactive')],
            // ['profile_verification_status', config('nest.common_status.Active')],
            ['users.id', '!=', $userID],
        ];

        $matchList = User::with([
            'userFamilyInfo:id,users_id,countries_id,states_id,districts_id',
            'userFamilyInfo.userDistrict:district_name,id',
            'userFamilyInfo.userState:state_name,id',
            'userFamilyInfo.userCountry:country_name,id',
            'userReligion:religion_name,id', 'userHeightList:height,height_value,id', 'userCaste:caste_name,id', 'userEducationSubcategory:id,edu_category_title',
            'userJobSubCategory:subcategory_name,job_new_sub_categories.id,parent_child_job_new_mapping.job_category_id',
            'userImage' => function ($query) {
                $query->where('image_approve', config('nest.image.approved'));
                $query->orderBy('is_preference', 'desc');
            },
            'familyIncomePhotoPassword' => function ($subquery) {
                $subquery->select('id', 'photo_is_protected', 'user_id');
            },
        ])
            ->where($whereConditions)
            ->whereDoesntHave('blockedUserList', function ($block) {
                $block->where('block_users.users_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            })
            ->whereDoesntHave('blockedByUserList', function ($block) {
                $block->where('block_users.block_profile_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            });

        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $matchList->whereBetween('users.created_at', [$startDate, $endDate]);
        }

        if ($profileCreatedBy) {
            $matchList->where('users.profile_created', $profileCreatedBy);
        }

        if ($gender) {
            $matchList->where('users.gender', '!=', $gender);
        }

        if ($filterReligion) {
            $matchList->where('users.religions_id', $filterReligion);

            if ($filterCaste) {
                $matchList->whereIn('users.caste_id', $filterCaste);
            }
        } else {
            if ($preferedReligion) {
                $matchList->where('users.religions_id', $preferedReligion);
            } else {
                if ($religion) {
                    $matchList->where('users.religions_id', $religion);
                }
            }

            if (is_array($preferedCaste) && !empty($preferedCaste)) {
                $matchList->whereIn('users.caste_id', $preferedCaste);
            } else if (is_array($intCaste) && !empty($intCaste)) {
                $matchList->whereIn('users.caste_id', $intCaste);
            } else {
                $matchList->where('users.caste_id', $caste);
            }
        }

        $ageFiltered = false;
        if ($ageFrom) {
            $matchList->where('users.age', '>=', $ageFrom);
            $ageFiltered = true;
        }
        if ($ageTo) {
            $matchList->where('users.age', '<=', $ageTo);
            $ageFiltered = true;
        }
        if (!$ageFiltered) {
            if ($preferedAgeFrom) {
                $matchList->whereBetween('users.age', [$preferedAgeFrom, $preferedAgeTo]);
            } else if ($age) {
                $matchList->whereBetween('users.age', [$age_from, $age_to]);
            }
        }

        $heightToFiltered = false;
        $heighFiltered = false;
        if ($heightFrom) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightFrom, $heightTo, &$heightToFiltered) {
                $q->where('height_list.height_value', '>=', $heightFrom);
                if ($heightTo) {
                    $q->where('height_list.height_value', '<=', $heightTo);
                    $heightToFiltered = true;
                }
            });
            $heighFiltered = true;
        }

        if (!$heightToFiltered && $heightTo) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightTo) {
                $q->where('height_list.height_value', '<=', $heightTo);
            });
            $heighFiltered = true;
        }

        if (!$heighFiltered) {
            if ($preferedHeightFrom) {
                $matchList->whereBetween('users.height_list_id', [$preferedHeightFrom, $preferedHeightTo]);
            } elseif ($height) {
                $matchList->whereBetween('users.height_list_id', [$height_from, $height_to]);
            }
        }

        if ($martialStatus) {
            $matchList->whereIn('users.marital_status_id', $martialStatus);
        } else {
            if (is_array($preferedMaritalStatus) && !empty($preferedMaritalStatus)) {
                $matchList->whereIn('users.marital_status_id', $preferedMaritalStatus);
            } elseif (!empty($marital_status)) {
                if ($marital_status == $mari_stat) {
                    $matchList->where('users.marital_status_id', $marital_status);
                } else {
                    $matchList->where('users.marital_status_id', '!=', $mari_stat);
                }
            }
        }

        if (is_array($preferedComplexion) && !empty($preferedComplexion) && !in_array($anyComplexionId, $preferedComplexion)) {
            $matchList->whereIn('users.complexion_id', $preferedComplexion);
        }

        if (is_array($preferedBodyType) && !empty($preferedBodyType) && !in_array($anyBodytypeId, $preferedBodyType)) {
            $matchList->whereIn('users.body_types_id', $preferedBodyType);
        }

        if ($education) {
            $matchList->where(function ($query) use ($education) {
                $query->WhereIn('users.education_category_id', $education);
            });
        } else {
            if (is_array($preferedEducation) && !empty($preferedEducation)) {
                $matchList->whereIn('users.education_category_id', $preferedEducation);
            } else {
                if (is_array($intEdu) && !empty($intEdu)) {
                    $matchList->whereIn('users.education_category_id', $intEdu);
                } else {
                    if ($education_id) {
                        $matchList->where('users.education_category_id', $education_id);
                    }
                }
            }
        }

        if ($job) {
            $matchList->where(function ($query) use ($job) {
                $query->WhereIn('users.job_score_id', $job);
            });
        } else {
            if (is_array($preferedJob) && !empty($preferedJob)) {
                $matchList->whereIn('users.job_score_id', $preferedJob)
                    ->whereNotNull('users.job_score_id')
                    ->whereNotNull('users.job_category_id')
                    ->whereColumn('users.job_score_id', 'users.job_category_id');
            }
        }

        if ($filterCountry) {
            $matchList->whereHas('userFamilyInfo', function ($q) use ($filterCountry, $filterState, &$filterDistrict) {
                $q->where('user_family_info.countries_id', $filterCountry);
                if ($filterState) {
                    $q->where('user_family_info.states_id', $filterState);
                    if ($filterDistrict) {
                        $q->whereIn('user_family_info.districts_id', $filterDistrict);
                    }
                }
            });
        } else if ($preferedCountry) {
            $matchList->whereHas('userFamilyInfo', function ($q) use ($preferedCountry, $preferedState) {
                $q->where('user_family_info.countries_id', $preferedCountry);
                if (is_array($preferedState) && !empty($preferedState)) {
                    $q->whereIn('user_family_info.states_id', $preferedState);
                }
            });
        } else {
            $stateFiltered = false;
            if ($country) {
                $matchList->whereHas('userFamilyInfo', function ($q) use ($country, $state, &$stateFiltered) {
                    $q->where('user_family_info.countries_id', $country);
                    if ($state) {
                        $q->where('user_family_info.states_id', $state);
                        $stateFiltered = true;
                    }
                });
            }
            if (!$stateFiltered && $state) {
                $matchList->whereHas('userFamilyInfo', function ($q) use ($state) {
                    $q->where('user_family_info.states_id', $state);
                });
            }
        }

        if (!empty($preferedSpecialCase) && is_array($preferedComplexion)) {
            $matchList->whereIn('users.special_case_id', $preferedSpecialCase);
        }

        if ($preferedJathakamType) {
            if (is_array($preferedJathakamType) && !empty($preferedJathakamType)) {
                $matchList->whereHas('userReligiousInfo', function ($q) use ($preferedJathakamType) {
                    $q->whereIn('user_religious_info.jathakam_types_id', $preferedJathakamType);
                });
            } else if (is_numeric($preferedJathakamType)) {
                $matchList->whereHas('userReligiousInfo', function ($q) use ($preferedJathakamType) {
                    $q->where('user_religious_info.jathakam_types_id', $preferedJathakamType);
                });
            }
        }

        return $matchList;
    }

    public function premiumMatchFilter($search_params)
    {
        $martialStatus = $search_params['marital_status'] ?? null;
        $heightFrom = $search_params['height_from'] ?? null;
        $heightTo = $search_params['height_to'] ?? null;
        $ageFrom = $search_params['age_from'] ?? null;
        $ageTo = $search_params['age_to'] ?? null;
        $filterReligion = $search_params['religion'] ?? null;
        $filterCaste = $search_params['caste'] ?? null;
        $education = $search_params['education'] ?? null;
        $job = $search_params['job'] ?? null;
        $profileCreatedBy = $search_params['profile_created_by'] ?? null;
        $profileCreatedAt = $search_params['profile_created_at'] ?? null;
        $filterCountry = $search_params['country'] ?? null;
        $filterState = $search_params['state'] ?? null;
        $filterDistrict = $search_params['district'] ?? null;

        #fetching last 6 months
        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $endDate = now();
            $startDate = $endDate->copy()->subMonth($profileCreatedAt);
        }

        $userID = Auth::user()->id;

        $getUserPref = UserPartnerPreference::where('users_id', $userID)->first();
        if (isset($getUserPref)) {
            $preferedReligion = $getUserPref->religions_id;
            $preferedCaste = unserialize($getUserPref->caste_id);

            $preferAgeFromId = $getUserPref->prefer_age_from_id;
            $preferAgeFromValue = Age::select('age')->where('id', $preferAgeFromId)->first();
            $preferedAgeFrom = $preferAgeFromValue ? $preferAgeFromValue->age : null;

            $preferedAgeToId = $getUserPref->prefer_age_to_id;
            $preferAgeToValue = Age::select('age')->where('id', $preferedAgeToId)->first();
            $preferedAgeTo = $preferAgeToValue ? $preferAgeToValue->age : null;

            $preferedHeightFrom = $getUserPref->prefer_height_from_id;

            $preferedHeightTo = $getUserPref->prefer_height_to_id;

            $preferedMaritalStatus = unserialize($getUserPref->marital_status_id);
        }

        $basicInfo = User::find($userID);
        $familyInfo = UserFamilyInfo::where('users_id', $userID)->first();
        $gender = $basicInfo->gender;
        $religion = $basicInfo->religions_id;
        $caste = $basicInfo->caste_id;


        $integratedCaste = CastIntegration::select('match_caste')->where('caste_id', $caste)->first();
        if (isset($integratedCaste->match_caste)) {
            $intCaste = unserialize($integratedCaste->match_caste);
        } else {
            $intCaste = array();
        }

        #age filter
        $age = $basicInfo->age;
        $age_from = getMinAge($age, $gender);
        $age_to = getMaxAge($age, $gender);

        #height filter
        $height = $basicInfo->height_list_id;
        $height_from = getMinHeight($height, $gender);
        $height_to = getMaxHeight($height, $gender);

        #martial status filter
        if ($basicInfo->marital_status_id) {
            $marital_status = $basicInfo->marital_status_id;
        }
        $mari_stat = config('nest.marital_status.never_married');

        $whereConditions = [
            // ['profile_verification_status', config('nest.common_status.Active')],
            // ['profile_hide', config('nest.common_status.Inactive')],
            // ['profile_active_status', config('nest.common_status.Active')],
            ['users.profile_complete', config('nest.common_status.Active')],
            ['users.is_deleted', config('nest.common_status.Inactive')],
            ['users.profile_hide', config('nest.common_status.Inactive')],
            // ['profile_verification_status', config('nest.common_status.Active')],
            // ['users.featured_profile_status', config('nest.common_status.Active')],
            ['users.id', '!=', $userID],
        ];

        $matchList = User::with([
            'userFamilyInfo:id,users_id,countries_id,states_id,districts_id',
            'userFamilyInfo.userDistrict:district_name,id',
            'userFamilyInfo.userState:state_name,id',
            'userFamilyInfo.userCountry:country_name,id',
            'userReligion:religion_name,id', 'userHeightList:height,height_value,id', 'userCaste:caste_name,id', 'userEducationSubcategory:id,edu_category_title',
            'userJobSubCategory:subcategory_name,job_new_sub_categories.id,parent_child_job_new_mapping.job_category_id',
            'userImage' => function ($query) {
                $query->where('image_approve', config('nest.image.approved'));
                $query->orderBy('is_preference', 'desc');
            },
            'familyIncomePhotoPassword' => function ($subquery) {
                $subquery->select('id', 'photo_is_protected', 'user_id');
            },
        ])
            ->where($whereConditions)
            ->whereIn('users.register_package_id', SubscriptionPlan::PREMIUM_PLAN)
            ->whereDoesntHave('blockedUserList', function ($block) {
                $block->where('block_users.users_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            })
            ->whereDoesntHave('blockedByUserList', function ($block) {
                $block->where('block_users.block_profile_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            });

        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $matchList->whereBetween('users.created_at', [$startDate, $endDate]);
        }

        if ($profileCreatedBy) {
            $matchList->where('users.profile_created', $profileCreatedBy);
        }

        if ($gender) {
            $matchList->where('users.gender', '!=', $gender);
        }

        if ($filterReligion) {
            $matchList->where('users.religions_id', $filterReligion);

            if ($filterCaste) {
                $matchList->whereIn('users.caste_id', $filterCaste);
            }
        } else {
            if ($preferedReligion) {
                $matchList->where('users.religions_id', $preferedReligion);
            } else {
                if ($religion) {
                    $matchList->where('users.religions_id', $religion);
                }
            }

            if (is_array($preferedCaste) && !empty($preferedCaste)) {
                $matchList->whereIn('users.caste_id', $preferedCaste);
            } else if (is_array($intCaste) && !empty($intCaste)) {
                $matchList->whereIn('users.caste_id', $intCaste);
            } else {
                $matchList->where('users.caste_id', $caste);
            }
        }

        $ageFiltered = false;
        if ($ageFrom) {
            $matchList->where('users.age', '>=', $ageFrom);
            $ageFiltered = true;
        }
        if ($ageTo) {
            $matchList->where('users.age', '<=', $ageTo);
            $ageFiltered = true;
        }
        if (!$ageFiltered) {
            if ($preferedAgeFrom) {
                $matchList->whereBetween('users.age', [$preferedAgeFrom, $preferedAgeTo]);
            } else if ($age) {
                $matchList->whereBetween('users.age', [$age_from, $age_to]);
            }
        }

        $heightToFiltered = false;
        $heighFiltered = false;
        if ($heightFrom) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightFrom, $heightTo, &$heightToFiltered) {
                $q->where('height_list.height_value', '>=', $heightFrom);
                if ($heightTo) {
                    $q->where('height_list.height_value', '<=', $heightTo);
                    $heightToFiltered = true;
                }
            });
            $heighFiltered = true;
        }

        if (!$heightToFiltered && $heightTo) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightTo) {
                $q->where('height_list.height_value', '<=', $heightTo);
            });
            $heighFiltered = true;
        }

        if (!$heighFiltered) {
            if ($preferedHeightFrom) {
                $matchList->whereBetween('users.height_list_id', [$preferedHeightFrom, $preferedHeightTo]);
            } else if ($height) {
                $matchList->whereBetween('users.height_list_id', [$height_from, $height_to]);
            }
        }

        if ($martialStatus) {
            $matchList->whereIn('users.marital_status_id', $martialStatus);
        } else {
            if (is_array($preferedMaritalStatus) && !empty($preferedMaritalStatus)) {
                $matchList->whereIn('users.marital_status_id', $preferedMaritalStatus);
            } elseif (!empty($marital_status)) {
                if ($marital_status == $mari_stat) {
                    $matchList->where('users.marital_status_id', $marital_status);
                } else {
                    $matchList->where('users.marital_status_id', '!=', $mari_stat);
                }
            }
        }

        if ($education) {
            $matchList->where(function ($query) use ($education) {
                $query->WhereIn('users.education_category_id', $education);
            });
        }

        if ($job) {
            $matchList->where(function ($query) use ($job) {
                $query->WhereIn('users.job_score_id', $job);
            });
        }

        if ($filterCountry) {
            $matchList->whereHas('userFamilyInfo', function ($q) use ($filterCountry, $filterState, &$filterDistrict) {
                $q->where('user_family_info.countries_id', $filterCountry);
                if ($filterState) {
                    $q->where('user_family_info.states_id', $filterState);
                    if ($filterDistrict) {
                        $q->whereIn('user_family_info.districts_id', $filterDistrict);
                    }
                }
            });
        }

        return $matchList;
    }

    public function nearByMatchFilter($search_params)
    {
        $martialStatus = $search_params['marital_status'] ?? null;
        $heightFrom = $search_params['height_from'] ?? null;
        $heightTo = $search_params['height_to'] ?? null;
        $ageFrom = $search_params['age_from'] ?? null;
        $ageTo = $search_params['age_to'] ?? null;
        $filterReligion = $search_params['religion'] ?? null;
        $filterCaste = $search_params['caste'] ?? null;
        $education = $search_params['education'] ?? null;
        $job = $search_params['job'] ?? null;
        $profileCreatedBy = $search_params['profile_created_by'] ?? null;
        $profileCreatedAt = $search_params['profile_created_at'] ?? null;
        $filterCountry = $search_params['country'] ?? null;
        $filterState = $search_params['state'] ?? null;
        $filterDistrict = $search_params['district'] ?? null;

        #fetching last 6 months
        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $endDate = now();
            $startDate = $endDate->copy()->subMonth($profileCreatedAt);
        }

        $userID = Auth::user()->id;

        $getUserPref = UserPartnerPreference::where('users_id', $userID)->first();
        if (isset($getUserPref)) {
            $preferedReligion = $getUserPref->religions_id;
            $preferedCaste = unserialize($getUserPref->caste_id);

            $preferAgeFromId = $getUserPref->prefer_age_from_id;
            $preferAgeFromValue = Age::select('age')->where('id', $preferAgeFromId)->first();
            $preferedAgeFrom = $preferAgeFromValue ? $preferAgeFromValue->age : null;

            $preferedAgeToId = $getUserPref->prefer_age_to_id;
            $preferAgeToValue = Age::select('age')->where('id', $preferedAgeToId)->first();
            $preferedAgeTo = $preferAgeToValue ? $preferAgeToValue->age : null;

            $preferedHeightFrom = $getUserPref->prefer_height_from_id;

            $preferedHeightTo = $getUserPref->prefer_height_to_id;

            $preferedEducation = unserialize($getUserPref->education_category_id);

            $preferedMaritalStatus = unserialize($getUserPref->marital_status_id);
        }

        $basicInfo = User::find($userID);
        $gender = $basicInfo->gender;
        $religion = $basicInfo->religions_id;
        $caste = $basicInfo->caste_id;

        $marital_status = $basicInfo->marital_status_id;
        $mari_stat = config('nest.marital_status.never_married');

        $integratedCaste = CastIntegration::select('match_caste')->where('caste_id', $caste)->first();

        if (isset($integratedCaste->match_caste)) {
            $intCaste = unserialize($integratedCaste->match_caste);
        } else {
            $intCaste = array();
        }

        #age filter
        $age = $basicInfo->age;
        $age_from = getMinAge($age, $gender);
        $age_to = getMaxAge($age, $gender);

        #height filter
        $height = $basicInfo->height_list_id;
        $height_from = getMinHeight($height, $gender);
        $height_to = getMaxHeight($height, $gender);

        #qualification filter
        $intEdu = array();
        $education_id = null;
        if ($basicInfo->education_category_id) {
            $education_id = $basicInfo->education_category_id;
            $integratedEducation = EducationIntegration::select('match_education')->where('education_id', $education_id)->first();
            if (isset($integratedEducation)) {
                $intEdu = unserialize($integratedEducation->match_education);
            } else {
                $intEdu = array();
            }
        }

        $country = Auth::user()->userFamilyInfo->countries_id;
        $state = Auth::user()->userFamilyInfo->states_id;
        $district = Auth::user()->userFamilyInfo->districts_id;

        $whereConditions = [
            // ['profile_verification_status', config('nest.common_status.Active')],
            // ['profile_hide', config('nest.common_status.Inactive')],
            // ['profile_active_status', config('nest.common_status.Active')],
            ['users.profile_complete', config('nest.common_status.Active')],
            ['users.is_deleted', config('nest.common_status.Inactive')],
            // ['profile_verification_status', config('nest.common_status.Active')],
            ['users.id', '!=', $userID],
        ];

        $matchList = User::with([
            'userFamilyInfo:id,users_id,countries_id,states_id,districts_id',
            'userFamilyInfo.userDistrict:district_name,id',
            'userFamilyInfo.userState:state_name,id',
            'userFamilyInfo.userCountry:country_name,id',
            'userReligion:religion_name,id', 'userHeightList:height,height_value,id', 'userCaste:caste_name,id', 'userEducationSubcategory:id,edu_category_title',
            'userJobSubCategory:subcategory_name,job_new_sub_categories.id,parent_child_job_new_mapping.job_category_id',
            'userImage' => function ($query) {
                $query->where('image_approve', config('nest.image.approved'));
                $query->orderBy('is_preference', 'desc');
            },
            'familyIncomePhotoPassword' => function ($subquery) {
                $subquery->select('id', 'photo_is_protected', 'user_id');
            },
        ])
            ->where($whereConditions)
            ->whereDoesntHave('blockedUserList', function ($block) {
                $block->where('block_users.users_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            })
            ->whereDoesntHave('blockedByUserList', function ($block) {
                $block->where('block_users.block_profile_id', Auth::user()->id);
                $block->where('block_users.status', config('nest.blocked_status.Block'));
            });

        if ($profileCreatedAt && $profileCreatedAt != config('nest.settings.created_any_time')) {
            $matchList->whereBetween('users.created_at', [$startDate, $endDate]);
        }

        if ($profileCreatedBy) {
            $matchList->where('users.profile_created', $profileCreatedBy);
        }

        if ($gender) {
            $matchList->where('users.gender', '!=', $gender);
        }

        if ($filterReligion) {
            $matchList->where('users.religions_id', $filterReligion);

            if ($filterCaste) {
                $matchList->whereIn('users.caste_id', $filterCaste);
            }
        } else {
            if ($preferedReligion) {
                $matchList->where('users.religions_id', $preferedReligion);
            } else {
                if ($religion) {
                    $matchList->where('users.religions_id', $religion);
                }
            }

            if (is_array($preferedCaste) && !empty($preferedCaste)) {
                $matchList->whereIn('users.caste_id', $preferedCaste);
            } else if (is_array($intCaste) && !empty($intCaste)) {
                $matchList->whereIn('users.caste_id', $intCaste);
            } else {
                $matchList->where('users.caste_id', $caste);
            }
        }

        $ageFiltered = false;
        if ($ageFrom) {
            $matchList->where('users.age', '>=', $ageFrom);
            $ageFiltered = true;
        }
        if ($ageTo) {
            $matchList->where('users.age', '<=', $ageTo);
            $ageFiltered = true;
        }
        if (!$ageFiltered) {
            if ($preferedAgeFrom) {
                $matchList->whereBetween('users.age', [$preferedAgeFrom, $preferedAgeTo]);
            } else if ($age) {
                $matchList->whereBetween('users.age', [$age_from, $age_to]);
            }
        }

        $heightToFiltered = false;
        $heighFiltered = false;
        if ($heightFrom) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightFrom, $heightTo, &$heightToFiltered) {
                $q->where('height_list.height_value', '>=', $heightFrom);
                if ($heightTo) {
                    $q->where('height_list.height_value', '<=', $heightTo);
                    $heightToFiltered = true;
                }
            });
            $heighFiltered = true;
        }

        if (!$heightToFiltered && $heightTo) {
            $matchList->whereHas('userHeightList', function ($q) use ($heightTo) {
                $q->where('height_list.height_value', '<=', $heightTo);
            });
            $heighFiltered = true;
        }

        if (!$heighFiltered) {
            if ($preferedHeightFrom) {
                $matchList->whereBetween('users.height_list_id', [$preferedHeightFrom, $preferedHeightTo]);
            } else if ($height) {
                $matchList->whereBetween('users.height_list_id', [$height_from, $height_to]);
            }
        }

        if ($martialStatus) {
            $matchList->whereIn('users.marital_status_id', $martialStatus);
        } else {

            if (is_array($preferedMaritalStatus) && !empty($preferedMaritalStatus)) {
                $matchList->whereIn('users.marital_status_id', $preferedMaritalStatus);
            } elseif (!empty($marital_status)) {
                if ($marital_status == $mari_stat) {
                    $matchList->where('users.marital_status_id', $marital_status);
                } else {
                    $matchList->where('users.marital_status_id', '!=', $mari_stat);
                }
            }
        }

        if ($education) {
            $matchList->where(function ($query) use ($education) {
                $query->WhereIn('users.education_category_id', $education);
            });
        } else {
            if (is_array($preferedEducation) && !empty($preferedEducation)) {
                $matchList->whereIn('users.education_category_id', $preferedEducation);
            } else {
                if (is_array($intEdu) && !empty($intEdu)) {
                    $matchList->whereIn('users.education_category_id', $intEdu);
                } else {
                    if ($education_id) {
                        $matchList->where('users.education_category_id', $education_id);
                    }
                }
            }
        }

        if ($job) {
            $matchList->where(function ($query) use ($job) {
                $query->WhereIn('users.job_score_id', $job);
            });
        }

        if ($filterCountry) {
            $matchList->whereHas('userFamilyInfo', function ($q) use ($filterCountry, $filterState, &$filterDistrict) {
                $q->where('user_family_info.countries_id', $filterCountry);
                if ($filterState) {
                    $q->where('user_family_info.states_id', $filterState);
                    if ($filterDistrict) {
                        $q->whereIn('user_family_info.districts_id', $filterDistrict);
                    }
                }
            });
        } else {
            if ($country == config('nest.country_code_id.india') && $state == config('nest.state_code_id.kerala')) {
                $matchList->whereHas('userFamilyInfo', function ($q) use ($country, $state, &$district) {
                    $q->where('user_family_info.countries_id', $country);
                    if ($state) {
                        $q->where('user_family_info.states_id', $state);
                        if ($district) {
                            $q->where('user_family_info.districts_id', $district);
                        }
                    }
                });
            } else if ($state && $country == config('nest.country_code_id.india') && $state != config('nest.state_code_id.kerala')) {
                $matchList->whereHas('userFamilyInfo', function ($q) use ($country, $state) {
                    $q->where('user_family_info.countries_id', $country);
                    if ($state) {
                        $q->where('user_family_info.states_id', $state);
                    }
                });
            } else if ($country && $country != config('nest.country_code_id.india')) {
                $matchList->whereHas('userFamilyInfo', function ($q) use ($country) {
                    $q->where('user_family_info.countries_id', $country);
                });
            }
        }

        return $matchList;
    }
}

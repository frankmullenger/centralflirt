<?php
if (!defined('LACONICA')) { exit(1); }

/**
 * Table Definition for dating_profile
 */
require_once INSTALLDIR.'/classes/Memcached_DataObject.php';

class Dating_profile extends Memcached_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dating_profile';                  // table name
    public $id;                              // int(11)  not_null primary_key
    public $firstname;                       // string(255)  not_null
    public $lastname;                        // string(255)  
    public $address_1;                       // string(255)  
    public $city;                            // string(255)  
    public $state;                           // string(255)  
    public $country;                         // int(11)  
    public $postcode;                        // string(255)  
    public $bio;                             // string(255)  
    public $birthdate;                       // date(10)  binary
    public $sex;                             // int(11)  
    public $partner_sex;                     // int(11)  
    public $interested_in;                   // int(11)  
    public $url;                             // string(255)  
    public $profession;                      // string(255)  not_null binary
    public $headline;                        // string(255)  not_null binary
    public $height;                          // int(11)  
    public $hair;                            // int(11)  
    public $body_type;                       // int(11)  
    public $ethnicity;                       // int(11)  
    public $eye_colour;                      // int(11)  
    public $marital_status;                  // int(11)  
    public $have_children;                   // int(11)  
    public $smoke;                           // int(11)  
    public $drink;                           // int(11)  
    public $religion;                        // int(11)  
    public $languages;                       // string(100)  binary
    public $education;                       // int(11)  
    public $politics;                        // int(11)  
    public $best_feature;                    // int(11)  
    public $body_art;                        // int(11)  
    public $fun;                             // blob(65535)  not_null blob binary
    public $fav_spot;                        // blob(65535)  not_null blob binary
    public $fav_media;                       // blob(65535)  not_null blob binary
    public $first_date;                      // blob(65535)  not_null blob binary
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Dating_profile',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    /*
     * Constants for entering data in db
     */
    const SEX_MALE = 1;
    const SEX_FEMALE = 2;
    
    const INTEREST_DATING = 1;
    const INTEREST_ACTIVITY_PARTNER = 2;
    const INTEREST_FRIENDSHIP = 3;
    const INTEREST_MARRIAGE = 4;
    const INTEREST_RELATIONSHIP = 5;
    const INTEREST_INTIMATE_ENCOUNTER = 6;
    
    const HAIR_BLONDE = 1;
    const HAIR_BRUNETTE = 2;
    const HAIR_DARK_BROWN = 3;
    const HAIR_BLACK = 4;
    const HAIR_AUBURN = 5;
    const HAIR_RED = 6;
    const HAIR_GREY = 7;
    const HAIR_WHITE = 8;
    const HAIR_CLOSE_SHAVE = 9;
    const HAIR_CLEAN_BALD = 10;
    
    const BODY_SLENDER = 1;
    const BODY_ATHLETIC = 2;
    const BODY_AVERAGE = 3;
    const BODY_FEW_POUNDS = 4;
    const BODY_STOCKY = 5;
    const BODY_HEAVY = 6;
    
    const ETHNICITY_WHITE = 1;
    const ETHNICITY_ASIAN = 2;
    const ETHNICITY_BLACK = 3;
    const ETHNICITY_INDIAN = 4;
    const ETHNICITY_LATINO = 5;
    const ETHNICITY_MIDDLE_EAST = 6;
    const ETHNICITY_NATIVE_AMERICAN = 7;
    const ETHNICITY_PACIFIC_ISLANDER = 8;
    const ETHNICITY_OTHER = 9;
    
    const MARITAL_NEVER_MARRIED = 1;
    const MARITAL_DIVORCED = 2;
    const MARITAL_SEPERATED = 3;
    const MARITAL_WIDOWED = 4;
    const MARITAL_MARRIED = 5;
    
    const DO_YOU_NO = 1;
    const DO_YOU_OCCASIONALLY = 2;
    const DO_YOU_OFTEN = 3;
    
    const EYE_COLOUR_BLUE = 1;
    const EYE_COLOUR_BROWN = 2;
    const EYE_COLOUR_HAZEL = 3;
    const EYE_COLOUR_GREEN = 4;
    const EYE_COLOUR_GREY = 5;
    const EYE_COLOUR_DAVID_BOWIE = 6;
    
    const BEST_FEATURE_ARMS = 1;
    const BEST_FEATURE_CHEST = 2;
    const BEST_FEATURE_EYES = 3;
    const BEST_FEATURE_BELLY_BUTTON = 4;
    const BEST_FEATURE_BUTT = 5;
    const BEST_FEATURE_LEGS = 6;
    const BEST_FEATURE_CALVES = 7;
    const BEST_FEATURE_FEET = 8;
    const BEST_FEATURE_HAIR = 9;
    const BEST_FEATURE_LIPS = 10;
    const BEST_FEATURE_NECK = 11;
    const BEST_FEATURE_HANDS = 12;
    
    const TATS_PIERCINGS_DONT_WANT = 1;
    const TATS_PIERCINGS_WANT = 2;
    const TATS_PIERCINGS_SOME = 3;
    const TATS_PIERCINGS_MANY = 4;
    
    const FAITH_AGNOSTIC = 1;
    const FAITH_ATHIEST = 2;
    const FAITH_CHRISTIAN_CATHOLIC = 3;
    const FAITH_CHRISTIAN_OTHER = 4;
    const FAITH_BUDDHIST_TAOIST = 5;
    const FAITH_HINDU = 6;
    const FAITH_JEWISH = 7;
    const FAITH_MUSLIM_ISLAM = 8;
    const FAITH_SPIRITUAL = 9;
    const FAITH_OTHER = 10;
    
    const EDUCATION_HIGH_SCHOOL = 1;
    const EDUCATION_SOME_COLLEGE = 2;
    const EDUCATION_BACHELORS = 3;
    const EDUCATION_MASTERS = 4;
    const EDUCATION_PHD = 5;
    
    const LANGUAGE_ARABIC = 1;
    const LANGUAGE_CHINESE = 2;
    const LANGUAGE_DUTCH = 3;
    const LANGUAGE_ENGLISH = 4;
    const LANGUAGE_FRENCH = 5;
    const LANGUAGE_GERMAN = 6;
    const LANGUAGE_HEBREW = 7;
    const LANGUAGE_HINDI = 8;
    const LANGUAGE_ITALIAN = 9;
    const LANGUAGE_JAPANESE = 10;
    const LANGUAGE_NORWEGIAN = 11;
    const LANGUAGE_PORTUGESE = 12;
    const LANGUAGE_RUSSIAN = 13;
    const LANGUAGE_SPANISH = 14;
    const LANGUAGE_SWEDISH = 15;
    const LANGUAGE_OTHER = 16;
    
    const POLITICAL_ULTRA_CONSERVATIVE = 1;
    const POLITICAL_CONSERVATIVE = 2;
    const POLITICAL_MID_ROAD = 3;
    const POLITICAL_LIBERAL = 4;
    const POLITICAL_VERY_LIBERAL = 5;
    const POLITICAL_NON_CONFORMIST = 6;
    const POLITICAL_OTHER = 7;
    
    const CHILDREN_NO = 1;
    const CHILDREN_LIVE_HOME = 2;
    const CHILDREN_NOT_AT_HOME = 3;
    
    const HEIGHT_LESS_FIVE = 49;
    const HEIGHT_FIVE_ZERO = 50;
    const HEIGHT_FIVE_ONE = 51;
    const HEIGHT_FIVE_TWO = 52;
    const HEIGHT_FIVE_THREE = 53;
    const HEIGHT_FIVE_FOUR = 54;
    const HEIGHT_FIVE_FIVE = 55;
    const HEIGHT_FIVE_SIX = 56;
    const HEIGHT_FIVE_SEVEN = 57;
    const HEIGHT_FIVE_EIGHT = 58;
    const HEIGHT_FIVE_NINE = 59;
    const HEIGHT_FIVE_TEN = 510;
    const HEIGHT_FIVE_ELEVEN = 511;
    const HEIGHT_SIX_ZERO = 60;
    const HEIGHT_SIX_ONE = 61;
    const HEIGHT_SIX_TWO = 62;
    const HEIGHT_SIX_THREE = 63;
    const HEIGHT_SIX_FOUR = 64;
    const HEIGHT_SIX_FIVE = 65;
    const HEIGHT_SIX_SIX = 66;
    const HEIGHT_SIX_SEVEN = 67;
    const HEIGHT_SIX_EIGHT = 68;
    const HEIGHT_SIX_NINE = 69;
    const HEIGHT_SIX_TEN = 610;
    const HEIGHT_SIX_ELEVEN = 611;
    const HEIGHT_SEVEN_ZERO = 70;
    const HEIGHT_GREATER_SEVEN = 71;
    
    private $dateObject = null;
    
    function getProfile()
    {
        return Profile::staticGet('id', $this->id);
    }
    
    function setInterestTags($newInterests) 
    {
        return Dating_profile_tag::setTags($this->id, $this->id, $newInterests);
    }
    
    function getInterestTags()
    {
        return Dating_profile_tag::getTags($this->id, $this->id);
    }
    
    function getLanguages()
    {
        return explode(';', $this->languages);
    }
    
    function getBirthdate($format='Y-m-d') 
    {
        if (!empty($this->birthdate)) {
            $birthdate = new DateTime($this->birthdate);
            return $birthdate->format($format);
        }
        else {
            return null;
        }
    }
    
    function getAge()
    {
        if (!empty($this->birthdate)) {
            $birthdate = new DateTime($this->birthdate);
            $now = new DateTime();
            
            //TODO frank: sort this out to use the dateTime diff() function instead, not accurate
            $diff = $now->format('Y-m-d') - $birthdate->format('Y-m-d');
            return $diff;
        }
        else {
            return null;
        }
    }
    
    function getNiceSexList() 
    {
        return array(self::SEX_MALE => _('Male'), self::SEX_FEMALE => _('Female'));
    }
    
    function getNiceInterestList() 
    {
        return  array(self::INTEREST_DATING => _('Dating'),
                      self::INTEREST_ACTIVITY_PARTNER => _('Activity Partner'),
                      self::INTEREST_FRIENDSHIP => _('Friendship'),
                      self::INTEREST_MARRIAGE => _('Marriage'),
                      self::INTEREST_RELATIONSHIP => _('Relationship'),
                      self::INTEREST_INTIMATE_ENCOUNTER => _('Intimate Encounter'),
                      );
    }
    
    function getNiceHeightList()
    {
        return  array(self::HEIGHT_LESS_FIVE => _('< 5\''),
                        self::HEIGHT_FIVE_ZERO => _('5\''),
                        self::HEIGHT_FIVE_ONE => _('5\'1"'),
                        self::HEIGHT_FIVE_TWO => _('5\'2"'),
                        self::HEIGHT_FIVE_THREE => _('5\'3"'),
                        self::HEIGHT_FIVE_FOUR => _('5\'4"'),
                        self::HEIGHT_FIVE_FIVE => _('5\'5"'),
                        self::HEIGHT_FIVE_SIX => _('5\'6"'),
                        self::HEIGHT_FIVE_SEVEN => _('5\'7"'),
                        self::HEIGHT_FIVE_EIGHT => _('5\'8"'),
                        self::HEIGHT_FIVE_NINE => _('5\'9"'),
                        self::HEIGHT_FIVE_TEN => _('5\'10"'),
                        self::HEIGHT_FIVE_ELEVEN => _('5\'11"'),
                        self::HEIGHT_SIX_ZERO => _('6\''),
                        self::HEIGHT_SIX_ONE => _('6\'1"'),
                        self::HEIGHT_SIX_TWO => _('6\'2"'),
                        self::HEIGHT_SIX_THREE => _('6\'3"'),
                        self::HEIGHT_SIX_FOUR => _('6\'4"'),
                        self::HEIGHT_SIX_FIVE => _('6\'5"'),
                        self::HEIGHT_SIX_SIX => _('6\'6"'),
                        self::HEIGHT_SIX_SEVEN => _('6\'7"'),
                        self::HEIGHT_SIX_EIGHT => _('6\'8"'),
                        self::HEIGHT_SIX_NINE => _('6\'9"'),
                        self::HEIGHT_SIX_TEN => _('6\'10"'),
                        self::HEIGHT_SIX_ELEVEN => _('6\'11"'),
                        self::HEIGHT_SEVEN_ZERO => _('7\''),
                        self::HEIGHT_GREATER_SEVEN => _('> 7\''),
                      );
    }
    
    function getNiceHairList() 
    {
        return array(self::HAIR_BLONDE => _('Blonde'),
                        self::HAIR_BRUNETTE => _('Brunette'),
                        self::HAIR_DARK_BROWN => _('Dark Brown'),
                        self::HAIR_BLACK => _('Black'),
                        self::HAIR_AUBURN => _('Auburn'),
                        self::HAIR_RED => _('Red'),
                        self::HAIR_GREY => _('Grey'),
                        self::HAIR_WHITE => _('White'),
                        self::HAIR_CLOSE_SHAVE => _('Close Shave'),
                        self::HAIR_CLEAN_BALD => _('Clean Bald'),
                    );
    }
    
    function getNiceBodytypeList()
    {
        return array(self::BODY_SLENDER => _('Slender'),
                        self::BODY_ATHLETIC => _('Athletic'),
                        self::BODY_AVERAGE => _('Average'),
                        self::BODY_FEW_POUNDS => _('Few Extra Pounds'),
                        self::BODY_STOCKY => _('Stocky'),
                        self::BODY_HEAVY => _('Heavy Set'),
                    );
    }
    
    function getNiceEthnicityList()
    {
        return array(self::ETHNICITY_WHITE => _('White / Caucasian'),
                        self::ETHNICITY_ASIAN => _('Asian'),
                        self::ETHNICITY_BLACK => _('Black / African descent'),
                        self::ETHNICITY_INDIAN => _('East Indian'),
                        self::ETHNICITY_LATINO => _('Latino / Hispanic'),
                        self::ETHNICITY_MIDDLE_EAST => _('Middle Eastern'),
                        self::ETHNICITY_NATIVE_AMERICAN => _('Native American'),
                        self::ETHNICITY_PACIFIC_ISLANDER => _('Pacific Islander'),
                        self::ETHNICITY_OTHER => _('Other / Not of this earth'),
                    );
    }
    
    function getNiceEyeColourList()
    {
        return array(self::EYE_COLOUR_BLUE => _('Blue'),
                        self::EYE_COLOUR_BROWN => _('Brown'),
                        self::EYE_COLOUR_HAZEL => _('Hazel'),
                        self::EYE_COLOUR_GREEN => _('Green'),
                        self::EYE_COLOUR_GREY => _('Grey'),
                        self::EYE_COLOUR_DAVID_BOWIE => _('David Bowie'),
                    );
    }
    
    function getNiceMaritalStatusList()
    {
        return array(self::MARITAL_NEVER_MARRIED => _('Never Married'),
                        self::MARITAL_DIVORCED => _('Divorced'),
                        self::MARITAL_SEPERATED => _('Seperated'),
                        self::MARITAL_WIDOWED => _('Widowed'),
                        self::MARITAL_MARRIED => _('Married'),
                    );
    }
    
    function getNiceHaveChildrenStatusList()
    {
        return array(self::CHILDREN_NO => _('No'),
                        self::CHILDREN_LIVE_HOME => _('Yes, they live at home'),
                        self::CHILDREN_NOT_AT_HOME => _('Yes, they don\'t live at home'),
                    );
    }
    
    function getNiceDoYouStatusList()
    {
        return array(self::DO_YOU_NO => _('No'),
                        self::DO_YOU_OCCASIONALLY => _('Occasionally'),
                        self::DO_YOU_OFTEN => _('Often'),
                    );
    }
    
    function getNiceReligionStatusList()
    {
        return array(self::FAITH_AGNOSTIC => _('Agnostic'),
                        self::FAITH_ATHIEST => _('Athiest'),
                        self::FAITH_CHRISTIAN_CATHOLIC => _('Christian / Catholic'),
                        self::FAITH_CHRISTIAN_OTHER => _('Christian / Other'),
                        self::FAITH_BUDDHIST_TAOIST => _('Buddhist / Taoist'),
                        self::FAITH_HINDU => _('Hindu'),
                        self::FAITH_JEWISH => _('Jewish'),
                        self::FAITH_MUSLIM_ISLAM => _('Muslim / Islam'),
                        self::FAITH_SPIRITUAL => _('Spritiual without religion'),
                        self::FAITH_OTHER => _('Other'),
                    );
    }
    
    function getNiceLanguageStatusList()
    {
        return array(self::LANGUAGE_ARABIC => _('Arabic'),
                        self::LANGUAGE_CHINESE => _('Chinese'),
                        self::LANGUAGE_DUTCH => _('Dutch'),
                        self::LANGUAGE_ENGLISH => _('English'),
                        self::LANGUAGE_FRENCH => _('French'),
                        self::LANGUAGE_GERMAN => _('German'),
                        self::LANGUAGE_HEBREW => _('Hebrew'),
                        self::LANGUAGE_HINDI => _('Hindi'),
                        self::LANGUAGE_ITALIAN => _('Italian'),
                        self::LANGUAGE_JAPANESE => _('Japanese'),
                        self::LANGUAGE_NORWEGIAN => _('Norwegian'),
                        self::LANGUAGE_PORTUGESE => _('Portugese'),
                        self::LANGUAGE_RUSSIAN => _('Russian'),
                        self::LANGUAGE_SPANISH => _('Spanish'),
                        self::LANGUAGE_SWEDISH => _('Swedish'),
                        self::LANGUAGE_OTHER => _('Other'),
                    );
    }
    
    function getNiceEducationStatusList()
    {
        return array(self::EDUCATION_HIGH_SCHOOL => _('High School'),
                        self::EDUCATION_SOME_COLLEGE => _('Some COllege'),
                        self::EDUCATION_BACHELORS => _('Bachelors Degree'),
                        self::EDUCATION_MASTERS => _('Masters Degree'),
                        self::EDUCATION_PHD => _('PhD'),
                    );
    }
    
    function getNicePoliticsStatusList()
    {
        return array(self::POLITICAL_ULTRA_CONSERVATIVE => _('Ultra Conservative'),
                        self::POLITICAL_CONSERVATIVE => _('Conservative'),
                        self::POLITICAL_MID_ROAD => _('Middle Of The Road'),
                        self::POLITICAL_LIBERAL => _('Liberal'),
                        self::POLITICAL_VERY_LIBERAL => _('Very Liberal'),
                        self::POLITICAL_NON_CONFORMIST => _('Non Conformist'),
                        self::POLITICAL_OTHER => _('Other'),
                    );
    }
    
    function getNiceBestFeatureStatusList()
    {
        return array(self::BEST_FEATURE_ARMS => _('Arms'),
                        self::BEST_FEATURE_CHEST => _('Chest'),
                        self::BEST_FEATURE_EYES => _('Eyes'),
                        self::BEST_FEATURE_BELLY_BUTTON => _('Belly Button'),
                        self::BEST_FEATURE_BUTT => _('Butt'),
                        self::BEST_FEATURE_LEGS => _('Legs'),
                        self::BEST_FEATURE_CALVES => _('Calves'),
                        self::BEST_FEATURE_FEET => _('Feet'),
                        self::BEST_FEATURE_HAIR => _('Hair'),
                        self::BEST_FEATURE_LIPS => _('Lips'),
                        self::BEST_FEATURE_NECK => _('Neck'),
                        self::BEST_FEATURE_HANDS => _('Hands'),
                    );
    }
    
    function getNiceBodyArtStatusList()
    {
        return array(self::TATS_PIERCINGS_DONT_WANT => _('I don\'t want any peircings/tattoos'),
                        self::TATS_PIERCINGS_WANT => _('I would like some peircings/tattos'),
                        self::TATS_PIERCINGS_SOME => _('I have a few piercings/tattos'),
                        self::TATS_PIERCINGS_MANY => _('I have many piercings/tattos'),
                    );
    }
    
    function getNiceYearList() {
        
        //TODO frank: need to change this so that its flexible when year increases
        
        $yearList = array();
        for ($i=1950; $i<1991; $i++) {
            $yearList[$i] = $i;
        }
        return $yearList;
    }
    
    function getNiceMonthList() {
        return array(1 => _('January'),
                     2 => _('February'),
                     3 => _('March'),
                     4 => _('April'),
                     5 => _('May'),
                     6 => _('June'),
                     7 => _('July'),
                     8 => _('August'),
                     9 => _('September'),
                     10 => _('October'),
                     11 => _('November'),
                     12 => _('December')
                    );
    }
    
    function getNiceMonthDayList() {
        $monthDays = array();
        for ($i=1; $i<=31; $i++) {
            $monthDays[$i] = $i;
        }
        return $monthDays;
    }
    
    /**
     * Overriding function to limit database queries to MySQL
     * TODO frank: Support Postgres and Sphinx for dating profiles
     * @see Memcached_DataObject::getSearchEngine()
     *
     * @param string $table
     */
    function getSearchEngine($table) {
        require_once INSTALLDIR.'/lib/search_engines.php';
        static $search_engine;
        
        if (!isset($search_engine)) {

            if ('mysql' === common_config('db', 'type')) {
                $search_engine = new MySQLSearch($this, $table);
            } else {
                //TODO frank: throw an exception here if the db is NOT MySQL
            }
        }
        return $search_engine;
    }
    
    /**
     * Dating profiles use the same key as the profile and user accounts
     */
    function sequenceKey() {
        return array(false,false);
    }
    
}

<?php
/**
 * Table Definition for dating_profile
 */
require_once 'DB/DataObject.php';

class Dating_profile extends DB_DataObject 
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
    public $birthdate_year;                  // int(11)  
    public $birthdate_month;                 // int(4)  
    public $birthdate_day;                   // int(4)  
    public $sex;                             // int(11)  
    public $partner_sex;                     // int(11)  
    public $interested_in;                   // int(11)  
    public $url;                             // string(255)  
    public $created;                         // datetime(19)  not_null binary
    public $modified;                        // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Dating_profile',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    /*
     * TODO create constants here for sex, interested_in fields
     * constants for city, state and country should probably go in the database
     * maybe another table that records the most popular cities in order to make filling out the profile easier?
     */
    const SEX_MALE = 1;
    const SEX_FEMALE = 2;
    
    const INTEREST_DATING = 1;
    const INTEREST_ACTIVITY_PARTNER = 2;
    const INTEREST_FRIENDSHIP = 3;
    const INTEREST_MARRIAGE = 4;
    const INTEREST_RELATIONSHIP = 5;
    const INTEREST_INTIMATE_ENCOUNTER = 6;
    
    private $dateObject = null;

    
    function getProfile()
    {
        return Profile::staticGet('id', $this->id);
    }
    
    function getNiceSexList() {
        
        return array(self::SEX_MALE => _('Male'), self::SEX_FEMALE => _('Female'));
    }
    
    function getNiceInterestList() {
        
        return  array(self::INTEREST_DATING => _('Dating'),
                      self::INTEREST_ACTIVITY_PARTNER => _('Activity Partner'),
                      self::INTEREST_FRIENDSHIP => _('Friendship'),
                      self::INTEREST_MARRIAGE => _('Marriage'),
                      self::INTEREST_RELATIONSHIP => _('Relationship'),
                      self::INTEREST_INTIMATE_ENCOUNTER => _('Intimate Encounter'),
                      );
    }
    
    function getNiceYearList() {
        $yearList = array();
        for ($i=1950; $i<2000; $i++) {
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
}

<?php

return [

    /*
     * The property id of which you want to display data.
     */
    'property_id' => env('ANALYTICS_PROPERTY_ID'),
    'property_code' => env('ANALYTICS_PROPERTY_CODE'),

    /*
     * Path to the client secret json file. Take a look at the README of this package
     * to learn how to get this file. You can also pass the credentials as an array
     * instead of a file path.
     */
    'service_account_credentials_json' => storage_path('app/analytics/service-account-credentials.json'),

    /*
     * The amount of minutes the Google API responses will be cached.
     * If you set this to zero, the responses won't be cached at all.
     */
    'cache_lifetime_in_minutes' => 60 * 24,

    /*
     * Here you may configure the "store" that the underlying Google_Client will
     * use to store it's data.  You may also add extra parameters that will
     * be passed on setCacheConfig (see docs for google-api-php-client).
     *
     * Optional parameters: "lifetime", "prefix"
     */
    'cache' => [
        'store' => 'file',
    ],

    'code_country' => [
        "Afghanistan" => "AF", "Albania" => "AL", "Algeria" => "DZ", "Andorra" => "AD", "Angola" => "AO",
        "Argentina" => "AR", "Armenia" => "AM", "Australia" => "AU", "Austria" => "AT", "Azerbaijan" => "AZ",
        "Bahamas" => "BS", "Bahrain" => "BH", "Bangladesh" => "BD", "Belarus" => "BY", "Belgium" => "BE",
        "Belize" => "BZ", "Benin" => "BJ", "Bhutan" => "BT", "Bolivia" => "BO", "Bosnia and Herzegovina" => "BA",
        "Botswana" => "BW", "Brazil" => "BR", "Brunei" => "BN", "Bulgaria" => "BG", "Burkina Faso" => "BF",
        "Burundi" => "BI", "Cambodia" => "KH", "Cameroon" => "CM", "Canada" => "CA", "Central African Republic" => "CF",
        "Chad" => "TD", "Chile" => "CL", "China" => "CN", "Colombia" => "CO", "Congo" => "CG", "Costa Rica" => "CR",
        "Croatia" => "HR", "Cuba" => "CU", "Cyprus" => "CY", "Czech Republic" => "CZ", "Denmark" => "DK", "Djibouti" => "DJ",
        "Dominican Republic" => "DO", "Ecuador" => "EC", "Egypt" => "EG", "El Salvador" => "SV", "Estonia" => "EE",
        "Ethiopia" => "ET", "Fiji" => "FJ", "Finland" => "FI", "France" => "FR", "Gabon" => "GA", "Gambia" => "GM",
        "Georgia" => "GE", "Germany" => "DE", "Ghana" => "GH", "Greece" => "GR", "Guatemala" => "GT", "Haiti" => "HT",
        "Honduras" => "HN", "Hungary" => "HU", "Iceland" => "IS", "India" => "IN", "Indonesia" => "ID", "Iran" => "IR",
        "Iraq" => "IQ", "Ireland" => "IE", "Israel" => "IL", "Italy" => "IT", "Jamaica" => "JM", "Japan" => "JP",
        "Jordan" => "JO", "Kazakhstan" => "KZ", "Kenya" => "KE", "Kuwait" => "KW", "Laos" => "LA", "Latvia" => "LV",
        "Lebanon" => "LB", "Libya" => "LY", "Lithuania" => "LT", "Luxembourg" => "LU", "Madagascar" => "MG",
        "Malawi" => "MW", "Malaysia" => "MY", "Maldives" => "MV", "Mali" => "ML", "Malta" => "MT", "Mauritania" => "MR",
        "Mexico" => "MX", "Moldova" => "MD", "Monaco" => "MC", "Mongolia" => "MN", "Montenegro" => "ME", "Morocco" => "MA",
        "Mozambique" => "MZ", "Myanmar" => "MM", "Namibia" => "NA", "Nepal" => "NP", "Netherlands" => "NL", "New Zealand" => "NZ",
        "Nicaragua" => "NI", "Niger" => "NE", "Nigeria" => "NG", "North Korea" => "KP", "Norway" => "NO", "Oman" => "OM",
        "Pakistan" => "PK", "Palestine" => "PS", "Panama" => "PA", "Papua New Guinea" => "PG", "Paraguay" => "PY",
        "Peru" => "PE", "Philippines" => "PH", "Poland" => "PL", "Portugal" => "PT", "Qatar" => "QA", "Romania" => "RO",
        "Russia" => "RU", "Rwanda" => "RW", "Saudi Arabia" => "SA", "Senegal" => "SN", "Serbia" => "RS", "Singapore" => "SG",
        "Slovakia" => "SK", "Slovenia" => "SI", "Somalia" => "SO", "South Africa" => "ZA", "South Korea" => "KR",
        "Spain" => "ES", "Sri Lanka" => "LK", "Sudan" => "SD", "Sweden" => "SE", "Switzerland" => "CH", "Syria" => "SY",
        "Taiwan" => "TW", "Tanzania" => "TZ", "Thailand" => "TH", "Togo" => "TG", "Tunisia" => "TN", "Turkey" => "TR",
        "Uganda" => "UG", "Ukraine" => "UA", "United Arab Emirates" => "AE", "United Kingdom" => "GB", "United States" => "US",
        "Uruguay" => "UY", "Uzbekistan" => "UZ", "Venezuela" => "VE", "Vietnam" => "VN", "Yemen" => "YE", "Zambia" => "ZM",
        "Zimbabwe" => "ZW"
    ],
];

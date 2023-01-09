<?php

namespace App\Services\AudioFetcher;

use Symfony\Component\HttpClient\HttpClient;
use App\Services\AudioFetcher\Resources\Surah;
use App\Services\AudioFetcher\Resources\BitRate;
use App\Services\AudioFetcher\Resources\Reciter;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AudioFetcher
{
    public function __construct(
        private HttpClientInterface $client,
        private ResourceBuilder $resourceBuilder,
        private KernelInterface $kernelInterface,
    ) {
        $store = new Store($kernelInterface->getProjectDir() . '/var/cache/data');
        $this->client = new CachingHttpClient($client, $store);
    }

    public function fetchLanguages(): array
    {
        $languages = [];
        $response = $this->client->request(
            'GET',
            'http://api.alquran.cloud/v1/edition/language'
        );

        $content = $response->toArray();

        foreach ($content['data'] as $language) {
            $languages[$language] = $this->getLanguageInfo($language);
        }

        // dd($languages);
        return $languages;
    }

    public function fetchTypes(): array
    {
        $types = [];
        $response = $this->client->request(
            'GET',
            'http://api.alquran.cloud/v1/edition/type'
        );

        $content = $response->toArray();

        foreach ($content['data'] as $type) {
            $types[] = $type;
        }
        return $types;
    }

    public function fetchEditions(string $language): array
    {
        $editions = [];

        $response = $this->client->request(
            'GET',
            'http://api.alquran.cloud/v1/edition/language/'. $language
        );

        $content = $response->toArray();

        foreach ($content as $reciter) {
            $reciter['bitrate'] = $this->resourceBuilder->createResource(BitRate::class, ['name' => $reciter['bitrate']]);
            array_push($editions, $this->resourceBuilder->createResource(Reciter::class, $reciter));
        }

        return $editions;
    }

    public function fetchSuwar(): array
    {
        $suwar = [];

        $response = $this->client->request(
            'GET',
            'http://api.alquran.cloud/v1/surah'
        );

        $content = $response->toArray();

        foreach ($content['data'] as $surah) {
            array_push($suwar, $this->resourceBuilder->createResource(Surah::class, $surah));
        }
        return $suwar;
    }

    public function fetchAudioVerses(Reciter $reciter, Surah $surah, int $fromVerse, int $toVerse): array
    {
        $audios = [];

        for ($i = $fromVerse; $i <= $toVerse; $i++) {
            $filename = $this->pad($surah->getNumber()) . $this->pad($i) . '.mp3';
            $path = $this->kernelInterface->getProjectDir() . '/var/mp3/' . $filename;
            $response = $this->client->request(
                'GET',
                'https://www.everyayah.com/data/' . $reciter->getSubfolder() . '/' . $filename,
                [
                    'headers' => [
                        'Content-Type' => 'audio/mpeg',
                    ],
                ]
            );
            $audio = $response->getContent();

            try {
                $stream = fopen($path, 'w+', false);
                file_put_contents($path, $audio);
                fclose($stream);
                $audios[] = $path;
            } catch (\Throwable $th) {
                throw $th;
            }
        }

        return $audios;
    }

    private function pad($n)
    {
        $n = explode('.', (string)$n);

        if (1 === count($n)) {
            return sprintf("%03d", $n[0]);
        } else if (2 === count($n)) {
            return sprintf("%02d", $n[0]);
        } else {
            implode('.', $n);
            return sprintf("%02d", $n[0]);
        }
    }

    /**
     * Encode array from latin1 to utf8 recursively
     * @param $dat
     * @return array|string
     */
    private static function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[$i] = self::convert_from_latin1_to_utf8_recursively($d);

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);

            return $dat;
        } else {
            return $dat;
        }
    }

    private static function getLanguageInfo($language): ?array
    {
        $languages = [
            "ab" => [
                "name" => "Abkhaz",
                "nativeName" => "аҧсуа"
            ],
            "aa" => [
                "name" => "Afar",
                "nativeName" => "Afaraf"
            ],
            "af" => [
                "name" => "Afrikaans",
                "nativeName" => "Afrikaans"
            ],
            "ak" => [
                "name" => "Akan",
                "nativeName" => "Akan"
            ],
            "sq" => [
                "name" => "Albanian",
                "nativeName" => "Shqip"
            ],
            "am" => [
                "name" => "Amharic",
                "nativeName" => "አማርኛ"
            ],
            "ar" => [
                "name" => "Arabic",
                "nativeName" => "العربية"
            ],
            "an" => [
                "name" => "Aragonese",
                "nativeName" => "Aragonés"
            ],
            "hy" => [
                "name" => "Armenian",
                "nativeName" => "Հայերեն"
            ],
            "as" => [
                "name" => "Assamese",
                "nativeName" => "অসমীয়া"
            ],
            "av" => [
                "name" => "Avaric",
                "nativeName" => "авар мацӀ, магӀарул мацӀ"
            ],
            "ae" => [
                "name" => "Avestan",
                "nativeName" => "avesta"
            ],
            "ay" => [
                "name" => "Aymara",
                "nativeName" => "aymar aru"
            ],
            "az" => [
                "name" => "Azerbaijani",
                "nativeName" => "azərbaycan dili"
            ],
            "bm" => [
                "name" => "Bambara",
                "nativeName" => "bamanankan"
            ],
            "ba" => [
                "name" => "Bashkir",
                "nativeName" => "башҡорт теле"
            ],
            "eu" => [
                "name" => "Basque",
                "nativeName" => "euskara, euskera"
            ],
            "be" => [
                "name" => "Belarusian",
                "nativeName" => "Беларуская"
            ],
            "bn" => [
                "name" => "Bengali",
                "nativeName" => "বাংলা"
            ],
            "bh" => [
                "name" => "Bihari",
                "nativeName" => "भोजपुरी"
            ],
            "bi" => [
                "name" => "Bislama",
                "nativeName" => "Bislama"
            ],
            "bs" => [
                "name" => "Bosnian",
                "nativeName" => "bosanski jezik"
            ],
            "br" => [
                "name" => "Breton",
                "nativeName" => "brezhoneg"
            ],
            "bg" => [
                "name" => "Bulgarian",
                "nativeName" => "български език"
            ],
            "my" => [
                "name" => "Burmese",
                "nativeName" => "ဗမာစာ"
            ],
            "ca" => [
                "name" => "Catalan; Valencian",
                "nativeName" => "Català"
            ],
            "ch" => [
                "name" => "Chamorro",
                "nativeName" => "Chamoru"
            ],
            "ce" => [
                "name" => "Chechen",
                "nativeName" => "нохчийн мотт"
            ],
            "ny" => [
                "name" => "Chichewa; Chewa; Nyanja",
                "nativeName" => "chiCheŵa, chinyanja"
            ],
            "zh" => [
                "name" => "Chinese",
                "nativeName" => "中文 (Zhōngwén), 汉语, 漢語"
            ],
            "cv" => [
                "name" => "Chuvash",
                "nativeName" => "чӑваш чӗлхи"
            ],
            "kw" => [
                "name" => "Cornish",
                "nativeName" => "Kernewek"
            ],
            "co" => [
                "name" => "Corsican",
                "nativeName" => "corsu, lingua corsa"
            ],
            "cr" => [
                "name" => "Cree",
                "nativeName" => "ᓀᐦᐃᔭᐍᐏᐣ"
            ],
            "hr" => [
                "name" => "Croatian",
                "nativeName" => "hrvatski"
            ],
            "cs" => [
                "name" => "Czech",
                "nativeName" => "česky, čeština"
            ],
            "da" => [
                "name" => "Danish",
                "nativeName" => "dansk"
            ],
            "dv" => [
                "name" => "Divehi; Dhivehi; Maldivian;",
                "nativeName" => "ދިވެހި"
            ],
            "nl" => [
                "name" => "Dutch",
                "nativeName" => "Nederlands, Vlaams"
            ],
            "en" => [
                "name" => "English",
                "nativeName" => "English"
            ],
            "eo" => [
                "name" => "Esperanto",
                "nativeName" => "Esperanto"
            ],
            "et" => [
                "name" => "Estonian",
                "nativeName" => "eesti, eesti keel"
            ],
            "ee" => [
                "name" => "Ewe",
                "nativeName" => "Eʋegbe"
            ],
            "fo" => [
                "name" => "Faroese",
                "nativeName" => "føroyskt"
            ],
            "fj" => [
                "name" => "Fijian",
                "nativeName" => "vosa Vakaviti"
            ],
            "fi" => [
                "name" => "Finnish",
                "nativeName" => "suomi, suomen kieli"
            ],
            "fr" => [
                "name" => "French",
                "nativeName" => "français, langue française"
            ],
            "ff" => [
                "name" => "Fula; Fulah; Pulaar; Pular",
                "nativeName" => "Fulfulde, Pulaar, Pular"
            ],
            "gl" => [
                "name" => "Galician",
                "nativeName" => "Galego"
            ],
            "ka" => [
                "name" => "Georgian",
                "nativeName" => "ქართული"
            ],
            "de" => [
                "name" => "German",
                "nativeName" => "Deutsch"
            ],
            "el" => [
                "name" => "Greek, Modern",
                "nativeName" => "Ελληνικά"
            ],
            "gn" => [
                "name" => "Guaraní",
                "nativeName" => "Avañeẽ"
            ],
            "gu" => [
                "name" => "Gujarati",
                "nativeName" => "ગુજરાતી"
            ],
            "ht" => [
                "name" => "Haitian; Haitian Creole",
                "nativeName" => "Kreyòl ayisyen"
            ],
            "ha" => [
                "name" => "Hausa",
                "nativeName" => "Hausa, هَوُسَ"
            ],
            "he" => [
                "name" => "Hebrew (modern)",
                "nativeName" => "עברית"
            ],
            "hz" => [
                "name" => "Herero",
                "nativeName" => "Otjiherero"
            ],
            "hi" => [
                "name" => "Hindi",
                "nativeName" => "हिन्दी, हिंदी"
            ],
            "ho" => [
                "name" => "Hiri Motu",
                "nativeName" => "Hiri Motu"
            ],
            "hu" => [
                "name" => "Hungarian",
                "nativeName" => "Magyar"
            ],
            "ia" => [
                "name" => "Interlingua",
                "nativeName" => "Interlingua"
            ],
            "id" => [
                "name" => "Indonesian",
                "nativeName" => "Bahasa Indonesia"
            ],
            "ie" => [
                "name" => "Interlingue",
                "nativeName" => "Originally called Occidental; then Interlingue after WWII"
            ],
            "ga" => [
                "name" => "Irish",
                "nativeName" => "Gaeilge"
            ],
            "ig" => [
                "name" => "Igbo",
                "nativeName" => "Asụsụ Igbo"
            ],
            "ik" => [
                "name" => "Inupiaq",
                "nativeName" => "Iñupiaq, Iñupiatun"
            ],
            "io" => [
                "name" => "Ido",
                "nativeName" => "Ido"
            ],
            "is" => [
                "name" => "Icelandic",
                "nativeName" => "Íslenska"
            ],
            "it" => [
                "name" => "Italian",
                "nativeName" => "Italiano"
            ],
            "iu" => [
                "name" => "Inuktitut",
                "nativeName" => "ᐃᓄᒃᑎᑐᑦ"
            ],
            "ja" => [
                "name" => "Japanese",
                "nativeName" => "日本語 (にほんご／にっぽんご)"
            ],
            "jv" => [
                "name" => "Javanese",
                "nativeName" => "basa Jawa"
            ],
            "kl" => [
                "name" => "Kalaallisut, Greenlandic",
                "nativeName" => "kalaallisut, kalaallit oqaasii"
            ],
            "kn" => [
                "name" => "Kannada",
                "nativeName" => "ಕನ್ನಡ"
            ],
            "kr" => [
                "name" => "Kanuri",
                "nativeName" => "Kanuri"
            ],
            "ks" => [
                "name" => "Kashmiri",
                "nativeName" => "कश्मीरी, كشميري‎"
            ],
            "kk" => [
                "name" => "Kazakh",
                "nativeName" => "Қазақ тілі"
            ],
            "km" => [
                "name" => "Khmer",
                "nativeName" => "ភាសាខ្មែរ"
            ],
            "ki" => [
                "name" => "Kikuyu, Gikuyu",
                "nativeName" => "Gĩkũyũ"
            ],
            "rw" => [
                "name" => "Kinyarwanda",
                "nativeName" => "Ikinyarwanda"
            ],
            "ky" => [
                "name" => "Kirghiz, Kyrgyz",
                "nativeName" => "кыргыз тили"
            ],
            "kv" => [
                "name" => "Komi",
                "nativeName" => "коми кыв"
            ],
            "kg" => [
                "name" => "Kongo",
                "nativeName" => "KiKongo"
            ],
            "ko" => [
                "name" => "Korean",
                "nativeName" => "한국어 (韓國語), 조선말 (朝鮮語)"
            ],
            "ku" => [
                "name" => "Kurdish",
                "nativeName" => "Kurdî, كوردی‎"
            ],
            "kj" => [
                "name" => "Kwanyama, Kuanyama",
                "nativeName" => "Kuanyama"
            ],
            "la" => [
                "name" => "Latin",
                "nativeName" => "latine, lingua latina"
            ],
            "lb" => [
                "name" => "Luxembourgish, Letzeburgesch",
                "nativeName" => "Lëtzebuergesch"
            ],
            "lg" => [
                "name" => "Luganda",
                "nativeName" => "Luganda"
            ],
            "li" => [
                "name" => "Limburgish, Limburgan, Limburger",
                "nativeName" => "Limburgs"
            ],
            "ln" => [
                "name" => "Lingala",
                "nativeName" => "Lingála"
            ],
            "lo" => [
                "name" => "Lao",
                "nativeName" => "ພາສາລາວ"
            ],
            "lt" => [
                "name" => "Lithuanian",
                "nativeName" => "lietuvių kalba"
            ],
            "lu" => [
                "name" => "Luba-Katanga",
                "nativeName" => ""
            ],
            "lv" => [
                "name" => "Latvian",
                "nativeName" => "latviešu valoda"
            ],
            "gv" => [
                "name" => "Manx",
                "nativeName" => "Gaelg, Gailck"
            ],
            "mk" => [
                "name" => "Macedonian",
                "nativeName" => "македонски јазик"
            ],
            "mg" => [
                "name" => "Malagasy",
                "nativeName" => "Malagasy fiteny"
            ],
            "ms" => [
                "name" => "Malay",
                "nativeName" => "bahasa Melayu, بهاس ملايو‎"
            ],
            "ml" => [
                "name" => "Malayalam",
                "nativeName" => "മലയാളം"
            ],
            "mt" => [
                "name" => "Maltese",
                "nativeName" => "Malti"
            ],
            "mi" => [
                "name" => "Māori",
                "nativeName" => "te reo Māori"
            ],
            "mr" => [
                "name" => "Marathi (Marāṭhī)",
                "nativeName" => "मराठी"
            ],
            "mh" => [
                "name" => "Marshallese",
                "nativeName" => "Kajin M̧ajeļ"
            ],
            "mn" => [
                "name" => "Mongolian",
                "nativeName" => "монгол"
            ],
            "na" => [
                "name" => "Nauru",
                "nativeName" => "Ekakairũ Naoero"
            ],
            "nv" => [
                "name" => "Navajo, Navaho",
                "nativeName" => "Diné bizaad, Dinékʼehǰí"
            ],
            "nb" => [
                "name" => "Norwegian Bokmål",
                "nativeName" => "Norsk bokmål"
            ],
            "nd" => [
                "name" => "North Ndebele",
                "nativeName" => "isiNdebele"
            ],
            "ne" => [
                "name" => "Nepali",
                "nativeName" => "नेपाली"
            ],
            "ng" => [
                "name" => "Ndonga",
                "nativeName" => "Owambo"
            ],
            "nn" => [
                "name" => "Norwegian Nynorsk",
                "nativeName" => "Norsk nynorsk"
            ],
            "no" => [
                "name" => "Norwegian",
                "nativeName" => "Norsk"
            ],
            "ii" => [
                "name" => "Nuosu",
                "nativeName" => "ꆈꌠ꒿ Nuosuhxop"
            ],
            "nr" => [
                "name" => "South Ndebele",
                "nativeName" => "isiNdebele"
            ],
            "oc" => [
                "name" => "Occitan",
                "nativeName" => "Occitan"
            ],
            "oj" => [
                "name" => "Ojibwe, Ojibwa",
                "nativeName" => "ᐊᓂᔑᓈᐯᒧᐎᓐ"
            ],
            "cu" => [
                "name" => "Old Church Slavonic, Church Slavic, Church Slavonic, Old Bulgarian, Old Slavonic",
                "nativeName" => "ѩзыкъ словѣньскъ"
            ],
            "om" => [
                "name" => "Oromo",
                "nativeName" => "Afaan Oromoo"
            ],
            "or" => [
                "name" => "Oriya",
                "nativeName" => "ଓଡ଼ିଆ"
            ],
            "os" => [
                "name" => "Ossetian, Ossetic",
                "nativeName" => "ирон æвзаг"
            ],
            "pa" => [
                "name" => "Panjabi, Punjabi",
                "nativeName" => "ਪੰਜਾਬੀ, پنجابی‎"
            ],
            "pi" => [
                "name" => "Pāli",
                "nativeName" => "पाऴि"
            ],
            "fa" => [
                "name" => "Persian",
                "nativeName" => "فارسی"
            ],
            "pl" => [
                "name" => "Polish",
                "nativeName" => "polski"
            ],
            "ps" => [
                "name" => "Pashto, Pushto",
                "nativeName" => "پښتو"
            ],
            "pt" => [
                "name" => "Portuguese",
                "nativeName" => "Português"
            ],
            "qu" => [
                "name" => "Quechua",
                "nativeName" => "Runa Simi, Kichwa"
            ],
            "rm" => [
                "name" => "Romansh",
                "nativeName" => "rumantsch grischun"
            ],
            "rn" => [
                "name" => "Kirundi",
                "nativeName" => "kiRundi"
            ],
            "ro" => [
                "name" => "Romanian, Moldavian, Moldovan",
                "nativeName" => "română"
            ],
            "ru" => [
                "name" => "Russian",
                "nativeName" => "русский язык"
            ],
            "sa" => [
                "name" => "Sanskrit (Saṁskṛta)",
                "nativeName" => "संस्कृतम्"
            ],
            "sc" => [
                "name" => "Sardinian",
                "nativeName" => "sardu"
            ],
            "sd" => [
                "name" => "Sindhi",
                "nativeName" => "सिन्धी, سنڌي، سندھی‎"
            ],
            "se" => [
                "name" => "Northern Sami",
                "nativeName" => "Davvisámegiella"
            ],
            "sm" => [
                "name" => "Samoan",
                "nativeName" => "gagana faa Samoa"
            ],
            "sg" => [
                "name" => "Sango",
                "nativeName" => "yângâ tî sängö"
            ],
            "sr" => [
                "name" => "Serbian",
                "nativeName" => "српски језик"
            ],
            "gd" => [
                "name" => "Scottish Gaelic; Gaelic",
                "nativeName" => "Gàidhlig"
            ],
            "sn" => [
                "name" => "Shona",
                "nativeName" => "chiShona"
            ],
            "si" => [
                "name" => "Sinhala, Sinhalese",
                "nativeName" => "සිංහල"
            ],
            "sk" => [
                "name" => "Slovak",
                "nativeName" => "slovenčina"
            ],
            "sl" => [
                "name" => "Slovene",
                "nativeName" => "slovenščina"
            ],
            "so" => [
                "name" => "Somali",
                "nativeName" => "Soomaaliga, af Soomaali"
            ],
            "st" => [
                "name" => "Southern Sotho",
                "nativeName" => "Sesotho"
            ],
            "es" => [
                "name" => "Spanish; Castilian",
                "nativeName" => "español, castellano"
            ],
            "su" => [
                "name" => "Sundanese",
                "nativeName" => "Basa Sunda"
            ],
            "sw" => [
                "name" => "Swahili",
                "nativeName" => "Kiswahili"
            ],
            "ss" => [
                "name" => "Swati",
                "nativeName" => "SiSwati"
            ],
            "sv" => [
                "name" => "Swedish",
                "nativeName" => "svenska"
            ],
            "ta" => [
                "name" => "Tamil",
                "nativeName" => "தமிழ்"
            ],
            "te" => [
                "name" => "Telugu",
                "nativeName" => "తెలుగు"
            ],
            "tg" => [
                "name" => "Tajik",
                "nativeName" => "тоҷикӣ, toğikī, تاجیکی‎"
            ],
            "th" => [
                "name" => "Thai",
                "nativeName" => "ไทย"
            ],
            "ti" => [
                "name" => "Tigrinya",
                "nativeName" => "ትግርኛ"
            ],
            "bo" => [
                "name" => "Tibetan Standard, Tibetan, Central",
                "nativeName" => "བོད་ཡིག"
            ],
            "tk" => [
                "name" => "Turkmen",
                "nativeName" => "Türkmen, Түркмен"
            ],
            "tl" => [
                "name" => "Tagalog",
                "nativeName" => "Wikang Tagalog, ᜏᜒᜃᜅ᜔ ᜆᜄᜎᜓᜄ᜔"
            ],
            "tn" => [
                "name" => "Tswana",
                "nativeName" => "Setswana"
            ],
            "to" => [
                "name" => "Tonga (Tonga Islands)",
                "nativeName" => "faka Tonga"
            ],
            "tr" => [
                "name" => "Turkish",
                "nativeName" => "Türkçe"
            ],
            "ts" => [
                "name" => "Tsonga",
                "nativeName" => "Xitsonga"
            ],
            "tt" => [
                "name" => "Tatar",
                "nativeName" => "татарча, tatarça, تاتارچا‎"
            ],
            "tw" => [
                "name" => "Twi",
                "nativeName" => "Twi"
            ],
            "ty" => [
                "name" => "Tahitian",
                "nativeName" => "Reo Tahiti"
            ],
            "ug" => [
                "name" => "Uighur, Uyghur",
                "nativeName" => "Uyƣurqə, ئۇيغۇرچە‎"
            ],
            "uk" => [
                "name" => "Ukrainian",
                "nativeName" => "українська"
            ],
            "ur" => [
                "name" => "Urdu",
                "nativeName" => "اردو"
            ],
            "uz" => [
                "name" => "Uzbek",
                "nativeName" => "zbek, Ўзбек, أۇزبېك‎"
            ],
            "ve" => [
                "name" => "Venda",
                "nativeName" => "Tshivenḓa"
            ],
            "vi" => [
                "name" => "Vietnamese",
                "nativeName" => "Tiếng Việt"
            ],
            "vo" => [
                "name" => "Volapük",
                "nativeName" => "Volapük"
            ],
            "wa" => [
                "name" => "Walloon",
                "nativeName" => "Walon"
            ],
            "cy" => [
                "name" => "Welsh",
                "nativeName" => "Cymraeg"
            ],
            "wo" => [
                "name" => "Wolof",
                "nativeName" => "Wollof"
            ],
            "fy" => [
                "name" => "Western Frisian",
                "nativeName" => "Frysk"
            ],
            "xh" => [
                "name" => "Xhosa",
                "nativeName" => "isiXhosa"
            ],
            "yi" => [
                "name" => "Yiddish",
                "nativeName" => "ייִדיש"
            ],
            "yo" => [
                "name" => "Yoruba",
                "nativeName" => "Yorùbá"
            ],
            "za" => [
                "name" => "Zhuang, Chuang",
                "nativeName" => "Saɯ cueŋƅ, Saw cuengh"
            ]
        ];

        return array_key_exists($language, $languages) ? $languages[$language] : [] ;
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum Language: string
{
    case EN = 'en';
    case FR = 'fr';
    case ES = 'es';
    case DE = 'de';
    case IT = 'it';
    case PT = 'pt';
    case RU = 'ru';
    case TR = 'tr';
    case JA = 'ja';
    case ZH = 'zh';
    case AR = 'ar';
    case HI = 'hi';
    case BN = 'bn';
    case TA = 'ta';
    case TE = 'te';
    case MR = 'mr';
    case GU = 'gu';
    case KN = 'kn';
    case ML = 'ml';
    case PA = 'pa';
    case SD = 'sd';
    case SY = 'sy';
    case UR = 'ur';
    case UK = 'uk';
    case VI = 'vi';
    case MS = 'ms';
    case NL = 'nl';
    case NO = 'no';
    case PL = 'pl';
    case RO = 'ro';
    case SK = 'sk';
    case SL = 'sl';
    case SV = 'sv';

    public function label(): string
    {
        return match ($this) {
            self::EN => 'English',
            self::FR => 'French',
            self::ES => 'Spanish',
            self::DE => 'German',
            self::IT => 'Italian',
            self::PT => 'Portuguese',
            self::RU => 'Russian',
            self::TR => 'Turkish',
            self::JA => 'Japanese',
            self::ZH => 'Chinese',
            self::AR => 'Arabic',
            self::HI => 'Hindi',
            self::BN => 'Bengali',
            self::TA => 'Tamil',
            self::TE => 'Telugu',
            self::MR => 'Marathi',
            self::GU => 'Gujarati',
            self::KN => 'Kannada',
            self::ML => 'Malayalam',
            self::PA => 'Punjabi',
            self::SD => 'Sindhi',
            self::SY => 'Syriac',
            self::UR => 'Urdu',
            self::UK => 'Ukrainian',
            self::VI => 'Vietnamese',
            self::MS => 'Malaysian',
            self::NL => 'Dutch',
            self::NO => 'Norwegian',
            self::PL => 'Polish',
            self::RO => 'Romanian',
            self::SK => 'Slovak',
            self::SL => 'Slovenian',
            self::SV => 'Swedish',
        };
    }
}

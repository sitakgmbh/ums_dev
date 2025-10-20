<?php

namespace App\Enums;

enum LogCategory: string
{
    case System = "system";
    case Database = "database";
    case Auth = "auth";
    case Api = "api";
	case Email = "mail";
	case Sap = "sap";
	case Antraege = "antraege";
	case Otobo = "otobo";
	case Graph = "graph";

    public function label(): string
    {
        return match($this) 
		{
            self::System => "System",
            self::Database => "Datenbank",
            self::Auth => "Authentifizierung",
            self::Api => "API",
			self::Email => "E-Mail",
			self::Sap => "SAP",
			self::Antraege => "Anträge",
			self::Otobo => "Otobo",
			self::Graph => "Microsoft Graph",
        };
    }

    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function isValid(string $value): bool
    {
        return in_array(strtolower($value), array_column(self::cases(), "value"), true);
    }
}

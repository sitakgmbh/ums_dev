<?php

namespace App\Utils;

use App\Models\Eroeffnung;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Utils\Logging\Logger;

class UserHelper
{
    private static function normalize(string $value): string
    {
        $replacements = [
            "ä"=>"ae","ö"=>"oe","ü"=>"ue","ß"=>"ss",
            "Ä"=>"Ae","Ö"=>"Oe","Ü"=>"Ue",
            "à"=>"a","á"=>"a","â"=>"a","ã"=>"a","å"=>"a","æ"=>"ae",
            "è"=>"e","é"=>"e","ê"=>"e","ë"=>"e",
            "ì"=>"i","í"=>"i","î"=>"i","ï"=>"i",
            "ò"=>"o","ó"=>"o","ô"=>"o","õ"=>"o","ø"=>"o",
            "ù"=>"u","ú"=>"u","û"=>"u",
            "ç"=>"c","ñ"=>"n",
            "š"=>"s","Š"=>"S","ž"=>"z","Ž"=>"Z",
            "ł"=>"l","Ł"=>"L","đ"=>"d","Đ"=>"D",
        ];
		
        return str_replace(" ", "", strtr($value, $replacements));
    }

    private static function usernameExists(string $username, ?int $ignoreId = null): bool
    {
        if (LdapUser::query()->whereEquals("samaccountname", $username)->exists()) 
		{
            return true;
        }

        $query = Eroeffnung::where("benutzername", $username)
            ->where("archiviert", 0);

        if ($ignoreId) 
		{
            $query->where("id", "!=", $ignoreId);
        }

        return $query->exists();
    }

    private static function emailExists(string $email, ?int $ignoreId = null): bool
    {
        if (LdapUser::query()->whereEquals("mail", $email)->exists()) 
		{
            return true;
        }

        $query = Eroeffnung::where("email", $email)->where("archiviert", 0);

        if ($ignoreId) 
		{
            $query->where("id", "!=", $ignoreId);
        }

        return $query->exists();
    }

    public static function generateUsername(string $vorname, string $nachname, ?int $ignoreId = null): string
    {
        $nachname = self::normalize($nachname);
        $vorname  = self::normalize($vorname);

        $base = strtolower(substr($nachname, 0, 3) . substr($vorname, 0, 3));
        $username = $base;
        $counter = 1;

        while (self::usernameExists($username, $ignoreId)) 
		{
            $username = $base . $counter;
            $counter++;

            if ($counter > 10) 
			{
                Logger::warning("Maximale Anzahl Versuche für Username erreicht ({$base})");
                break;
            }
        }

        return $username;
    }

    public static function generateEmail(string $vorname, string $nachname, string $domain, string $username, ?int $ignoreId = null): string
    {
        $domain = ltrim($domain, "@");
        $nachname = self::normalize($nachname);
        $vorname  = self::normalize($vorname);

        $email = strtolower("{$vorname}.{$nachname}@{$domain}");
        $counter = 1;

        while (self::emailExists($email, $ignoreId)) 
		{
            if (preg_match("/(\d+)$/", $username, $m)) 
			{
                $counter = (int) $m[1];
            }

            $email = strtolower("{$vorname}.{$nachname}{$counter}@{$domain}");
            $counter++;

            if ($counter > 10) 
			{
                Logger::warning("Maximale Anzahl Versuche für Email erreicht ({$email})");
                break;
            }
        }

        return $email;
    }

    public static function generatePassword(int $length = 8): string
    {
        $lower   = "abcdefghjkmnpqrstuvwxyz";
        $upper   = "ABCDEFGHJKLMNPQRSTUVWXYZ";
        $numbers = "23456789";
        $special = "!";

        $all = $lower . $upper . $numbers . $special;

        $password = [];
        $password[] = $lower[random_int(0, strlen($lower) - 1)];
        $password[] = $upper[random_int(0, strlen($upper) - 1)];
        $password[] = $numbers[random_int(0, strlen($numbers) - 1)];
        $password[] = $special[random_int(0, strlen($special) - 1)];

        while (count($password) < $length) 
		{
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle(implode("", $password));
    }
}

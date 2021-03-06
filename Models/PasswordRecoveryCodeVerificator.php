<?php
namespace Poznavacky\Models;

use Poznavacky\Models\Exceptions\DatabaseException;
use Poznavacky\Models\Statics\Db;

/**
 * Třída ověřující, zda je kód pro obnovu hesla platný a jakému patří uživateli
 * @author Jan Štěch
 */
class PasswordRecoveryCodeVerificator
{
    /**
     * Metoda získávající ID uživatele s jehož účtem je svázán kód pro obnovu hesla uložený v databázi
     * @param string $code Kód pro obnovu hesla z URL adresy
     * @return int|boolean ID uživatele, který může použít tento kód pro obnovu svého hesla nebo FALSE, pokud kód nebyl
     *     v databázi nalezen
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     */
    public function verifyCode(string $code)
    {
        $result = Db::fetchQuery('SELECT uzivatele_id FROM obnoveni_hesel WHERE kod = ? AND expirace > ?',
            array(md5($code), time()), false);
        if (!$result) {
            return false;
        }
        return $result['uzivatele_id'];
    }
    
    /**
     * Metoda odstraňující specifikovaný kód pro obnovu hesla z databáze
     * @param string $code Nezašifrovaný kód k odstranění
     * @throws DatabaseException Pokud se při práci s databází vyskytne chyba
     */
    public function deleteCode(string $code): void
    {
        Db::executeQuery('DELETE FROM obnoveni_hesel WHERE kod = ?', array(md5($code)));
    }
}


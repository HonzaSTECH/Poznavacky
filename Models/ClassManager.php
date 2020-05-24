<?php

/** 
 * Třída získávající informace o třídě z databáze, například za účelem pro získání ID z názvu a obráceně
 * Dále ověřuje zda třída nebo poznávačka do ní patřící existuje.
 * @author Jan Štěch
 */
class ClassManager
{
    /**
     * Metoda kontrolující, zda třída daného jména existuje
     * @param string $className Jméno třídy
     * @return boolean TRUE, pokud byla třída nalezene, FALSE, pokud ne
     */
    public static function classExists(string $className)
    {
        Db::connect();
        $cnt = Db::fetchQuery('SELECT COUNT(*) AS "cnt" FROM tridy WHERE nazev = ?', array($className), false);
        if ($cnt['cnt'] > 0)
        {
            return true;
        }
        return false;
    }
    
    /**
     * Metoda získávající z databáze seznam ID všech tříd, jejichž přístupový kód je nastaven na specifikovanou hodnotu
     * POZNÁMKA: V případě velkého množství tříd je možné omezit dotaz tak, aby navracel pouze soukromé třídy, jelikož do veřejných a uzamčených tříd se nedá získat členství
     * @param int $code Kód podle kterého vyhledáváme
     * @return array|boolean Pole ID tříd, které používají daný přístupový kód nebo FALSE, pokud žádné takové třídy neexistují
     */
    public static function getClassesByAccessCode($code)
    {
        Db::connect();
        $result = Db::fetchQuery('SELECT tridy_id FROM tridy WHERE kod = ?', array($code), true);
       #$result = Db::fetchQuery('SELECT tridy_id FROM tridy WHERE kod = ? AND status = ?', array($code, ClassObject::CLASS_STATUS_PRIVATE), true);
        
        //Kontrola, zda je navrácen alespoň jeden výsledek
        if (!$result)
        {
            return false;
        }
        
        //Převod dvourozměrného pole obsahujícím v každém řádku jednu hodnotu (id) do jednorozměrného (numerického)
        return array_map('current', $result);
    }
}
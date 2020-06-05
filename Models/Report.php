<?php
/** 
 * Třída reprezentující hlášení obrázku
 * @author Jan Štěch
 */
class Report
{
    const ADMIN_REQUIRING_REASONS = array(self::REASON_COPYRIGHT, self::REASON_OTHER_ADMIN);
    
    const REASON_NOT_DISPLAYING = 'Obrázek se nezobrazuje správně';
    const REASON_LONG_LOADING = 'Obrázek se načítá příliš dlouho';
    const REASON_INCORRECT_NATURAL = 'Obrázek zobrazuje nesprávnou přírodninu';
    const REASON_CONTAINS_NAME = 'Obrázek obsahuje název přírodniny';
    const REASON_BAD_RESOLUTION = 'Obrázek má příliš špatné rozlišení';
    const REASON_COPYRIGHT = 'Obrázek porušuje autorská práva';
    const REASON_OTHER = 'Jiný důvod (řeší správce třídy)';
    const REASON_OTHER_ADMIN = 'Jiný důvod (řeší správce služby)';
    
    private $id;
    private $picture;
    private $reason;
    private $additionalInformation;
    private $reportersCount;
    
    /**
     * Konstruktor hlášení nastavující všechny jeho vlastnosti
     * @param int $id ID hlášení
     * @param Picture $picture Odkaz na objekt obrázku, kterého se toto hlášení týká
     * @param string $reason Důvod hlášení (musí být hodnota jedne z konstant této třídy začánající na REASON_)
     * @param string $additionalInformation Další informace, které může uživatel u určitých hlášení specifikovat
     * @param int $reportersCount Počet uživatelů, kteří tento obrázek z tohoto důvodu nahlásili
     */
    public function __construct(int $id, Picture $picture, string $reason, string $additionalInformation = "", int $reportersCount)
    {
        $this->id = $id;
        $this->picture = $picture;
        $this->reason = $reason;
        $this->additionalInformation = $additionalInformation;
        $this->reportersCount = $reportersCount;
    }
    
    /**
     * Metoda navracející ID nahlášeného obrázku
     * @return int ID obrázku
     */
    public function getPictureId()
    {
        return $this->picture->getId();
    }
    
    /**
     * Metoda navracející URL nahlášeného obrázku
     * @return string Zdroj obrázku
     */
    public function getUrl()
    {
        return $this->picture->getSrc();
    }
    
    /**
     * Metoda navracející řetězec se zařazením obrázku ve formátu <Název třídy> / <Název poznávačky> / <Název části> / <Název přírodniny>
     * @return string Řetězec obsahující cestu k obrázku
     */
    public function getPicturePath()
    {
        $natural = $this->picture->getNatural();
        $part = $natural->getPart();
        $group = $part->getGroup();
        $class = $group->getClass();
        return $class->getName().' / '.$group->getName().' / '.$part->getName(). ' / '.$natural->getName();
    }
    
    /**
     * Metoda navracející důvod hlášení
     * @return string Důvod hlášení (měl by být jednou z konstant této třídy)
     */
    public function getReason()
    {
        return $this->reason;
    }
    
    /**
     * Metoda navracející další informace o hlášení
     * @return string Další informace o hlášení (pokud žádné nebyly poskytnuty, tak prázdný řetězec)
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }
    
    /**
     * Metoda navracející počet hlášení stejného typu
     * @return int Počet hlášení
     */
    public function getReportersCount()
    {
        return $this->reportersCount;
    }
}
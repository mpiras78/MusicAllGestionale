<?php
/**
 * Helper per calcolo pagamenti mensili
 */

class PagamentiHelper {
    
    /**
     * Calcola il numero di occorrenze di un giorno della settimana in un mese
     * 
     * @param string $mese_riferimento Formato YYYY-MM
     * @param int $giorno_settimana 1=Lunedì, 7=Domenica
     * @param array $festivita Array di date festive (YYYY-MM-DD)
     * @return array ['count' => int, 'date' => array]
     */
    public static function contaLezioniMese($mese_riferimento, $giorno_settimana, $festivita = []) {
        list($anno, $mese) = explode('-', $mese_riferimento);
        
        $primo_giorno = new DateTime("$anno-$mese-01");
        $ultimo_giorno = new DateTime($primo_giorno->format('Y-m-t'));
        
        $date_lezioni = [];
        $current = clone $primo_giorno;
        
        // Converti giorno_settimana (1-7) a formato DateTime (1=Monday, 7=Sunday)
        $target_day = $giorno_settimana;
        
        while ($current <= $ultimo_giorno) {
            if ($current->format('N') == $target_day) {
                $data_str = $current->format('Y-m-d');
                
                // Escludi festività
                if (!in_array($data_str, $festivita)) {
                    $date_lezioni[] = $data_str;
                }
            }
            $current->modify('+1 day');
        }
        
        return [
            'count' => count($date_lezioni),
            'date' => $date_lezioni
        ];
    }
    
    /**
     * Calcola l'importo mensile proporzionale
     * 
     * @param float $costo_base Costo mensile base (per 4 lezioni)
     * @param int $num_lezioni_effettive Numero lezioni nel mese
     * @param int $num_lezioni_base Numero lezioni base (default 4)
     * @return float Importo calcolato
     */
    public static function calcolaImportoMensile($costo_base, $num_lezioni_effettive, $num_lezioni_base = 4) {
        if ($num_lezioni_base == 0) {
            return 0;
        }
        
        $costo_per_lezione = $costo_base / $num_lezioni_base;
        return round($costo_per_lezione * $num_lezioni_effettive, 2);
    }
    
    /**
     * Calcola importo per iscrizione in un mese specifico
     * 
     * @param int $iscrizione_id ID iscrizione
     * @param string $mese_riferimento YYYY-MM
     * @return array ['importo' => float, 'num_lezioni' => int, 'dettaglio' => array]
     */
    public static function calcolaImportoIscrizione($iscrizione_id, $mese_riferimento) {
        $db = Database::getInstance()->getConnection();
        
        // Recupera iscrizione e tipo corso
        $stmt = $db->prepare("
            SELECT i.*, tc.costo_mensile, tc.durata_lezione
            FROM iscrizioni i
            JOIN tipi_corso_config tc ON i.tipo_corso_config_id = tc.id
            WHERE i.id = ?
        ");
        $stmt->execute([$iscrizione_id]);
        $iscrizione = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$iscrizione) {
            return ['importo' => 0, 'num_lezioni' => 0, 'dettaglio' => []];
        }
        
        // Recupera template lezioni (slot settimanali)
        $stmt = $db->prepare("
            SELECT giorno_settimana
            FROM lezioni
            WHERE iscrizione_id = ? AND attiva = 1
        ");
        $stmt->execute([$iscrizione_id]);
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($templates)) {
            return ['importo' => 0, 'num_lezioni' => 0, 'dettaglio' => []];
        }
        
        // Recupera festività del mese
        $festivita = self::getFestivitaMese($mese_riferimento);
        
        $totale_lezioni = 0;
        $dettaglio = [];
        
        foreach ($templates as $template) {
            $info = self::contaLezioniMese(
                $mese_riferimento, 
                $template['giorno_settimana'], 
                $festivita
            );
            
            $totale_lezioni += $info['count'];
            $dettaglio[] = [
                'giorno_settimana' => $template['giorno_settimana'],
                'num_lezioni' => $info['count'],
                'date' => $info['date']
            ];
        }
        
        $importo = self::calcolaImportoMensile(
            $iscrizione['costo_mensile'], 
            $totale_lezioni
        );
        
        return [
            'importo' => $importo,
            'num_lezioni' => $totale_lezioni,
            'costo_base' => $iscrizione['costo_mensile'],
            'dettaglio' => $dettaglio
        ];
    }
    
    /**
     * Recupera festività per un mese
     * 
     * @param string $mese_riferimento YYYY-MM
     * @return array Date festive (YYYY-MM-DD)
     */
    public static function getFestivitaMese($mese_riferimento) {
        $db = Database::getInstance()->getConnection();
        
        list($anno, $mese) = explode('-', $mese_riferimento);
        $primo = "$anno-$mese-01";
        $ultimo = date('Y-m-t', strtotime($primo));
        
        $stmt = $db->prepare("
            SELECT data_festivita
            FROM festivita
            WHERE data_festivita BETWEEN ? AND ?
        ");
        $stmt->execute([$primo, $ultimo]);
        
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'data_festivita');
    }
    
    /**
     * Genera preview calcolo per tutti i mesi dell'anno scolastico
     * 
     * @param int $iscrizione_id
     * @param string $anno_scolastico Es: "2024/2025"
     * @return array Mesi con importi calcolati
     */
    public static function previewAnnoScolastico($iscrizione_id, $anno_scolastico) {
        list($anno_inizio, $anno_fine) = explode('/', $anno_scolastico);
        
        $mesi = [];
        
        // Settembre - Dicembre
        for ($m = 9; $m <= 12; $m++) {
            $mese = sprintf("%04d-%02d", $anno_inizio, $m);
            $mesi[$mese] = self::calcolaImportoIscrizione($iscrizione_id, $mese);
        }
        
        // Gennaio - Giugno
        for ($m = 1; $m <= 6; $m++) {
            $mese = sprintf("%04d-%02d", $anno_fine, $m);
            $mesi[$mese] = self::calcolaImportoIscrizione($iscrizione_id, $mese);
        }
        
        return $mesi;
    }
}

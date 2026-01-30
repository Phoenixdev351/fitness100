<?php
/**
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class FeedType
{
    public function getType($mode)
    {
        $types = $this->getAllTypes();

        return $types[$mode];
    }

    public function getAllTypes()
    {
        return array(
            'c' => array(
                'name' => 'Individual',
                'category_name' => '',
                'country' => '',
            ),
            'f' => array(
                'name' => 'Facebook',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            'g' => array(
                'name' => 'Google',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            'y' => array(
                'name' => 'Yahoo',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            's' => array(
                'name' => 'Skroutz',
                'category_name' => '',
                'country' => 'greece',
            ),
            'bp' => array(
                'name' => 'Bestprice',
                'category_name' => 'Greece',
                'country' => 'greece',
            ),
            'i' => array(
                'name' => 'microspot',
                'category_name' => '',
                'country' => 'switzerland',
            ),
            'x' => array(
                'name' => 'Xikixi',
                'category_name' => '',
                'country' => 'united kingdom, mexico, chile, colombia, ecuador, portugal, france, germany, italy, brazil, spain, canada',
            ),
            'r' => array(
                'name' => 'Fruugo',
                'category_name' => 'fruugo',
                'category_id' => '2',
                'country' => 'global',
            ),
            'h' => array(
                'name' => 'Hansabay',
                'category_name' => '',
                'country' => 'estonia',
            ),
            'm' => array(
                'name' => 'Sitemap',
                'category_name' => '',
                'country' => 'global',
            ),
            'a' => array(
                'name' => 'Marktplaats',
                'category_name' => 'marktplaats',
                'category_id' => '8',
                'country' => 'netherlands, belgium',
            ),
            'o' => array(
                'name' => 'Shoptet',
                'category_name' => '',
                'country' => 'czech',
            ),
            'e' => array(
                'name' => 'Beslist.nl',
                'category_name' => 'beslist',
                'category_id' => '1',
                'country' => 'netherlands, belgium',
            ),
            'p' => array(
                'name' => 'Prisjakt',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'sweden, norway',
            ),
            'pdk' => array(
                'name' => 'Prisjagt',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'denmark',
            ),
            'pp' => array(
                'name' => 'PriceSpy',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'united kingdom, new zealand, australia',
            ),
            'hi' => array(
                'name' => 'Hintaopas',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'finland',
            ),
            'ld' => array(
                'name' => 'leDenicheur',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'france',
            ),
            'ko' => array(
                'name' => 'Kompario',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'poland',
            ),
            'u' => array(
                'name' => 'Heureka',
                'category_name' => 'heureka',
                'category_id' => '7',
                'country' => 'czech',
            ),
            'n' => array(
                'name' => 'PriceRunner',
                'category_name' => '',
                'country' => 'denmark, sweden, united kingdom',
            ),
            'k' => array(
                'name' => 'Kelkoo',
                'category_name' => '',
                'country' => 'united kingdom, portugal, france, germany, italy, spain, romania, greece, hungary, poland, sweden, denmark, norway',
            ),
            't' => array(
                'name' => 'Twenga',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'united kingdom, portugal, france, germany, italy, spain, poland, netherlands',
            ),
            'd' => array(
                'name' => 'idealo',
                'category_name' => '',
                'country' => 'germany, finland',
            ),
            'pint' => array(
                'name' => 'Pinterest',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            'sn' => array(
                'name' => 'Snapchat',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            'gla' => array(
                'name' => 'Glami',
                'category_name' => 'glami',
                'category_id' => '3',
                'country' => 'united kingdom, portugal, france, germany, italy, spain, czech, slovakia, romania, hungary, greece, latvia, lithuania, estonia, croatia, slovenia',
            ),
            'sa' => array(
                'name' => 'ShopAlike',
                'category_name' => '',
                'country' => 'united kingdom, portugal, france, italy, spain, poland, netherlands, austria, czech, denmark, finland, sweden, slovakia, hungary',
            ),
            'lz' => array(
                'name' => 'LadenZeile',
                'category_name' => '',
                'country' => 'germany',
            ),
            'st' => array(
                'name' => 'Stileo',
                'category_name' => '',
                'country' => 'italy',
            ),
            'mm' => array(
                'name' => 'ManoMano',
                'category_name' => '',
                'country' => 'france, spain, italy, germany, united kingdom',
            ),
            'vi' => array(
                'name' => 'Vivino',
                'category_name' => '',
                'country' => 'france, canada, united states, italy, spain',
            ),
            'sm' => array(
                'name' => 'ShopMania',
                'category_name' => '',
                'country' => 'global',
            ),
            'rd' => array(
                'name' => 'Rue Du Commerce',
                'category_name' => '',
                'country' => 'france, united states',
            ),
            'ws' => array(
                'name' => 'Wine-searcher',
                'category_name' => '',
                'country' => 'global',
            ),
            'dre' => array(
                'name' => 'Drezzy',
                'category_name' => '',
                'country' => 'italy',
            ),
            'cen' => array(
                'name' => 'Ceneje',
                'category_name' => '',
                'country' => 'slovenia, bosnia and herzegovina, croatia, serbia',
            ),
            'tro' => array(
                'name' => 'Trovaprezzi',
                'category_name' => '',
                'country' => 'italy',
            ),
            'ppy' => array(
                'name' => 'Shoppydoo',
                'category_name' => '',
                'country' => 'italy',
            ),
            'twe' => array(
                'name' => 'Tweakers',
                'category_name' => '',
                'country' => 'netherlands, belgium',
            ),
            'k24' => array(
                'name' => 'Kaina24',
                'category_name' => '',
                'country' => 'lithuania',
            ),
            'kos' => array(
                'name' => 'Kainos',
                'category_name' => '',
                'country' => 'lithuania',
            ),
            'plt' => array(
                'name' => 'Pricer',
                'category_name' => '',
                'country' => 'lithuania',
            ),
            'aru' => array(
                'name' => 'Arukereso',
                'category_name' => '',
                'country' => 'hungary',
            ),
            'com' => array(
                'name' => 'Compari',
                'category_name' => '',
                'country' => 'romania',
            ),
            'paz' => array(
                'name' => 'Pazaruvaj',
                'category_name' => '',
                'country' => 'bulgaria',
            ),
            'epr' => array(
                'name' => 'ePRICE',
                'category_name' => '',
                'country' => 'italy',
            ),
            'sez' => array(
                'name' => 'Seznam',
                'category_name' => '',
                'country' => 'czech',
            ),
            'pri' => array(
                'name' => 'Prisguiden',
                'category_name' => '',
                'country' => 'norway',
            ),
            'mal' => array(
                'name' => 'MALL',
                'category_name' => '',
                'category_id' => 9,
                'country' => 'czech',
            ),
            'spa' => array(
                'name' => 'Spartoo',
                'category_name' => '',
                'category_id' => 10,
                'country' => 'global',
            ),
            'ins' => array(
                'name' => 'Instagram',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            'lw' => array(
                'name' => 'LinkWise',
                'category_name' => '',
                'country' => 'greece, turkish',
            ),
            'naj' => array(
                'name' => 'najnakup',
                'category_name' => '',
                'country' => 'slovakia',
            ),
            'tot' => array(
                'name' => 'TOTOS',
                'category_name' => '',
                'country' => 'greece',
            ),
            'onb' => array(
                'name' => 'OnBuy',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'united kingdom, united states',
            ),
            'ceo' => array(
                'name' => 'Ceneo',
                'category_name' => '',
                'country' => 'poland',
            ),
            'bil' => array(
                'name' => 'billiger',
                'category_name' => '',
                'country' => 'germany',
            ),
            'sho' => array(
                'name' => 'SHOPPING',
                'category_name' => '',
                'country' => 'france, italy, germany, united kingdom, united states',
            ),
            'cj' => array(
                'name' => 'CJ Affiliate',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            'man' => array(
                'name' => 'Pricemania',
                'category_name' => '',
                'country' => 'slovakia, czech',
            ),
            'fav' => array(
                'name' => 'Favi',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'france, poland, romania, hungary, italy, sweden, united kingdom',
            ),
            'zbo' => array(
                'name' => 'Zbozi',
                'category_name' => 'heureka',
                'category_id' => '7',
                'country' => 'czech',
            ),
            'sal' => array(
                'name' => 'Salidzini',
                'category_name' => '',
                'country' => 'latvia',
            ),
            'pub' => array(
                'name' => 'Public.gr',
                'category_name' => '',
                'country' => 'greece',
            ),
            'hind' => array(
                'name' => 'Hind',
                'category_name' => '',
                'country' => 'estonia',
            ),
            'kurp' => array(
                'name' => 'kurpirkt',
                'category_name' => '',
                'country' => 'latvia',
            ),
            'hinn' => array(
                'name' => 'Hinnavaatlus',
                'category_name' => '',
                'country' => 'estonia',
            ),
            'wum' => array(
                'name' => 'wumler',
                'category_name' => '',
                'country' => 'global',
            ),
            'mala' => array(
                'name' => 'Malaseno',
                'category_name' => '',
                'country' => 'italy',
            ),
            'tc' => array(
                'name' => 'the clutcher',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            'lyst' => array(
                'name' => 'Lyst',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'united kingdom, united states',
            ),
            'wb' => array(
                'name' => 'webgains',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'united kingdom, united states, germany',
            ),
            'ikx' => array(
                'name' => 'iKRIX',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'united states, italy, france, spain',
            ),
            'cr' => array(
                'name' => 'comparer',
                'category_name' => '',
                'country' => 'belgium',
            ),
            'ver' => array(
                'name' => 'vertaa',
                'category_name' => '',
                'country' => 'finland',
            ),
            'verk' => array(
                'name' => 'vergelijk',
                'category_name' => '',
                'country' => 'netherlands, belgium, france',
            ),
            'tov' => array(
                'name' => 'tovar',
                'category_name' => '',
                'country' => 'slovakia',
            ),
            'wes' => array(
                'name' => 'webshopy',
                'category_name' => '',
                'country' => 'slovakia',
            ),
            'che' => array(
                'name' => 'cherchons',
                'category_name' => '',
                'country' => 'france',
            ),
            'kie' => array(
                'name' => 'kieskeurig',
                'category_name' => '',
                'country' => 'netherlands',
            ),
            'kog' => array(
                'name' => 'Kogan',
                'category_name' => 'kogan',
                'category_key' => 'kogan_ebay_en',
                'country' => 'australia',
            ),
            'mir' => array(
                'name' => 'mirakl',
                'category_name' => '',
                'country' => 'global',
            ),
            'cat' => array(
                'name' => 'Catch',
                'category_name' => '',
                'country' => 'australia',
            ),
            'dar' => array(
                'name' => 'Darty',
                'category_name' => '',
                'country' => 'france',
            ),
            'ibs' => array(
                'name' => 'IBS',
                'category_name' => '',
                'country' => 'italy',
            ),
            'ven' => array(
                'name' => 'Venca',
                'category_name' => '',
                'country' => 'spain, portugal',
            ),
            'pb' => array(
                'name' => 'Le Panier Bleu',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'canada, france',
            ),
            'wor' => array(
                'name' => 'worten',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'portugal, venezuela',
            ),
            'cri' => array(
                'name' => 'Criteo',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            'rol' => array(
                'name' => 'AdRoll',
                'category_name' => '',
                'country' => 'global',
            ),
            'tt' => array(
                'name' => 'TradeTracker',
                'category_name' => '',
                'country' => 'global',
            ),
            'dm' => array(
                'name' => 'Direct Market',
                'category_name' => '',
                'country' => 'greece',
            ),
            'pm' => array(
                'name' => 'ProfitMetrics',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'global',
            ),
            'ep' => array(
                'name' => 'epicentrk',
                'category_name' => '',
                'country' => 'ukraine, russia',
            ),
            'ro' => array(
                'name' => 'rozetka',
                'category_name' => '',
                'country' => 'ukraine',
            ),
            'ar' => array(
                'name' => 'argep',
                'category_name' => '',
                'country' => 'hungary',
            ),
            'ho' => array(
                'name' => 'hotline',
                'category_name' => '',
                'country' => 'ukraine',
            ),
            'ek' => array(
                'name' => 'E-Katalog',
                'category_name' => '',
                'country' => 'ukraine, russia',
            ),
            'kuk' => array(
                'name' => 'KuantoKusta',
                'category_name' => '',
                'country' => 'portugal',
            ),
            'dot' => array(
                'name' => 'dott',
                'category_name' => '',
                'country' => 'portugal',
            ),
            'pem' => array(
                'name' => 'Pemami',
                'category_name' => '',
                'country' => 'portugal',
            ),
            'gei' => array(
                'name' => 'Geizhals',
                'category_name' => '',
                'country' => 'austria, germany',
            ),
            'ski' => array(
                'name' => 'Skinflint',
                'category_name' => '',
                'country' => 'united kingdom',
            ),
            'cew' => array(
                'name' => 'Cenowarka',
                'category_name' => '',
                'country' => 'poland',
            ),
            'cb' => array(
                'name' => 'cool blue',
                'category_name' => '',
                'country' => 'belgium',
            ),
            'gu' => array(
                'name' => 'guenstiger',
                'category_name' => '',
                'country' => 'germany',
            ),
            'bi' => array(
                'name' => 'bike exchange',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'australia',
            ),
            'gp' => array(
                'name' => 'Get Price',
                'category_name' => '',
                'country' => 'australia',
            ),
            'hb' => array(
                'name' => 'homebook',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'poland',
            ),
            'sco' => array(
                'name' => 'scoupz',
                'category_name' => '',
                'country' => 'netherlands',
            ),
            'fc' => array(
                'name' => 'fashionchick',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'netherlands',
            ),
            'lbb' => array(
                'name' => 'Les Bonnes Bouilles',
                'category_name' => '',
                'country' => 'france',
            ),
            'pl' => array(
                'name' => 'Plytix',
                'category_name' => 'google',
                'category_id' => '4',
                'country' => 'united kingdom, united states, turkish',
            ),
            'ec' => array(
                'name' => 'eCommerce',
                'category_name' => '',
                'country' => 'united kingdom, italy, spain',
            ),
            'no' => array(
                'name' => 'nokaut',
                'category_name' => '',
                'country' => 'poland, czech',
            ),
            'gd' => array(
                'name' => 'gun.deals',
                'category_name' => '',
                'country' => 'united states',
            ),
            'sfl' => array(
                'name' => 'Shopflix',
                'category_name' => '',
                'country' => 'greece',
            ),
        );
    }

    public function getMostPopularTypes()
    {
        return array(
            'c' => array(
                'name' => 'Individual',
                'category_name' => '',
            ),
            'f' => array(
                'name' => 'Facebook',
                'category_name' => 'google',
                'category_id' => '4',
            ),
            'g' => array(
                'name' => 'Google',
                'category_name' => 'google',
                'category_id' => '4',
            ),
            's' => array(
                'name' => 'Skroutz',
                'category_name' => '',
            ),
            'd' => array(
                'name' => 'idealo',
                'category_name' => '',
            ),
            'n' => array(
                'name' => 'PriceRunner',
                'category_name' => '',
            ),
            'mm' => array(
                'name' => 'ManoMano',
                'category_name' => '',
            ),
            'tro' => array(
                'name' => 'Trovaprezzi',
                'category_name' => '',
            ),
        );
    }
}

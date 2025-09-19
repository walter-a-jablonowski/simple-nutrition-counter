<?php

use Symfony\Component\Yaml\Yaml;

require_once '../../vendor/autoload.php';

class PricesReportController
{
  private $foodsDir;

  public function __construct()
  {
    $cfg = [];
    if( file_exists(__DIR__ . '/config.yml'))
    {
      try { $cfg = Yaml::parseFile(__DIR__ . '/config.yml') ?: []; }
      catch( \Throwable $e ) { $cfg = []; }
    }
    $this->foodsDir = isset($cfg['foodsDir']) ? (string)$cfg['foodsDir'] : '../../data/bundles/Default_JaneDoe@example.com-24080101000000/foods';
  }

  public function getData( string $range = '6m') : array
  {
    $items = [];
    $now = new DateTime();
    $cutoff = $this->computeCutoff($now, $range);

    foreach( $this->scanFoods($this->foodsDir) as $name => $entry )
    {
      $curPrice = $this->toNumber($entry['price'] ?? '');
      $curDeal  = $this->toNumber($entry['dealPrice'] ?? '');
      $lastUpd  = isset($entry['lastPriceUpd']) ? (string)$entry['lastPriceUpd'] : '';
      // Normalize last update for display
      $lastOut = $lastUpd;
      if( $lastUpd !== '' )
      {
        if( preg_match('/^\d{4}-\d{2}-\d{2}$/', $lastUpd) ) {
          // already in YYYY-MM-DD, keep
          $lastOut = $lastUpd;
        }
        elseif( ctype_digit($lastUpd) ) {
          // likely epoch seconds => format to YYYY-MM-DD
          $ts = (int)$lastUpd;
          if( $ts > 0 ) $lastOut = gmdate('Y-m-d', $ts);
        }
      }

      $histP = $this->parseHistory($entry['prices'] ?? null);
      $histD = $this->parseHistory($entry['dealPrices'] ?? null);

      $firstP = $this->firstLevel($histP);
      $firstD = $this->firstLevel($histD);

      // Determine if we should show: only if current went above first level (price or deal)
      $show = false;
      $changePct = null; $changePctDeal = null;
      if( $curPrice !== null && $firstP !== null && $curPrice > $firstP ) {
        $show = true; $changePct = $firstP > 0 ? ($curPrice - $firstP) / $firstP * 100.0 : null;
      }
      if( $curDeal !== null && $firstD !== null && $curDeal > $firstD ) {
        $show = true; $changePctDeal = $firstD > 0 ? ($curDeal - $firstD) / $firstD * 100.0 : null;
      }

      // Filter by date range if provided and we have lastPriceUpd
      if( $show && $cutoff )
      {
        if( $lastUpd ) {
          try { $dt = new DateTime($lastUpd); } catch(\Throwable $e){ $dt = null; }
          if( $dt && $dt < $cutoff ) $show = false;
        } else {
          // no date -> exclude when filtering by range
          $show = false;
        }
      }

      if( ! $show ) continue;

      $items[$name] = [
        'name'        => $name,
        'price'       => $curPrice,
        'dealPrice'   => $curDeal,
        'firstPrice'  => $firstP,
        'firstDeal'   => $firstD,
        'pct'         => $changePct,
        'pctDeal'     => $changePctDeal,
        'lastPriceUpd'=> $lastOut,
      ];
    }

    // Sort by highest percentage change (consider price then deal)
    uasort($items, function($a, $b){
      $ap = max($a['pct'] ?? -INF, $a['pctDeal'] ?? -INF);
      $bp = max($b['pct'] ?? -INF, $b['pctDeal'] ?? -INF);
      if( $ap == $bp ) return strcmp($a['name'], $b['name']);
      return ($ap < $bp) ? 1 : -1;
    });

    return [
      'range' => $range,
      'items' => $items,
    ];
  }

  private function computeCutoff( DateTime $latest, string $range ) : ?DateTime
  {
    $dt = clone $latest;
    switch($range){
      case '1m': $dt->modify('-1 month'); return $dt;
      case '2m': $dt->modify('-2 months'); return $dt;
      case '3m': $dt->modify('-3 months'); return $dt;
      case '6m': $dt->modify('-6 months'); return $dt;
      case '1y': $dt->modify('-1 year');  return $dt;
      default: return null; // all
    }
  }

  private function scanFoods( string $dir ) : array
  {
    $foods = [];
    if( ! is_dir($dir)) return $foods;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach( $rii as $file )
    {
      if( $file->isDir()) continue;
      if( pathinfo($file->getFilename(), PATHINFO_EXTENSION) !== 'yml') continue;
      $fn   = $file->getFilename();
      $path = $file->getPathname();
      if( substr($fn, 0, 1) === '_') continue;
      try { $data = Yaml::parseFile($path); }
      catch( \Throwable $e ) { continue; }
      if( ! is_array($data)) continue;
      if( str_ends_with($fn, '-this.yml')) {
        $name = basename($file->getPath());
      } else {
        if( strpos($fn, '-this.yml') !== false ) continue; // skip any -this variants accidentally matched
        $name = pathinfo($fn, PATHINFO_FILENAME);
      }
      $foods[$name] = $data;
    }
    return $foods;
  }

  private function parseHistory( $hist ) : array
  {
    $out = [];
    if( ! is_array($hist)) return $out;
    foreach( $hist as $date => $val )
    {
      $num = $this->toNumber($val);
      if( $num === null) continue;
      // normalize date
      try { $dt = new DateTime((string)$date); } catch(\Throwable $e){ continue; }
      $out[$dt->format('Y-m-d')] = $num;
    }
    ksort($out);
    return $out;
  }

  private function firstLevel( array $hist ) : ?float
  {
    if( empty($hist)) return null;
    $keys = array_keys($hist);
    $firstKey = $keys[0];
    return $hist[$firstKey];
  }

  private function toNumber( $v ) : ?float
  {
    if( $v === null || $v === '') return null;
    if( is_numeric($v)) return 0 + $v;
    $s = str_replace(["\xC2\xA0", ' '], '', (string)$v);
    $s = str_replace(',', '.', $s);
    $parts = explode('.', $s);
    if( count($parts) > 2 ) $s = implode('', array_slice($parts, 0, -1)) . '.' . end($parts);
    return is_numeric($s) ? 0 + $s : null;
  }
}

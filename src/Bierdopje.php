<?php
namespace mattiasdelang;

use Carbon\Carbon;
use GuzzleHttp\Client;

class Bierdopje {

  /**
   * @var string
   */
  protected $url = 'http://api.bierdopje.com/';

  /**
   * @var Client client
   */
  protected $client;

  /**
   * Create Bierdopje API instance
   *
   * @param Client $client
   * @param null   $apiKey
   *
   * @throws \Exception
   */
  public function __construct(Client $client, $apiKey = null)
  {
    $apiKey = getenv('BD_APIKEY') ?: $apiKey;
    if ( ! $apiKey )
      throw new \Exception('Bierdopje Apikey not found in environment');

    $this->client = $client;
    $this->url .= $apiKey;
  }

  /**
   * Get a show by Bierdopje showId
   *
   * @param $showId
   *
   * @return \SimpleXMLElement[]|\stdClass
   * @throws \Exception
   */
  public function getShowById($showId)
  {
    $response = $this->request('/GetShowById/' . $showId);
    if ( $response->response->status == 'false' )
      return null;
    $show     = $response->response;

    $show = $this->formatShow($show);

    return $show;
  }

  /**
   * Search a show by name
   *
   * @param $showName
   *
   * @return null|\stdClass
   * @throws \Exception
   */
  public function getShowByName($showName)
  {
    $response = $this->request('/FindShowByName/' . $showName);
    if ( $response->response->status == 'false' )
      return null;
    $shows = $response->response->results->result;

    if ( count($shows) <= 0 )
      return null;

    $show = $shows[0];
    $show = $this->formatShow($show);

    return $show;
  }

  /**
   * Search a show by a TVDB showId
   *
   * @param $tvdbId
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function getShowByTvdbId($tvdbId)
  {
    $response = $this->request('/GetShowByTVDBID/' . $tvdbId);
    if ( $response->response->status == 'false' )
      return null;
    $show     = $this->formatShow($response->response);

    return $show;
  }

  /**
   * Search episodes by season and Bierdopje showId
   *
   * @param $showId
   * @param $season
   *
   * @return array|null
   * @throws \Exception
   */
  public function getEpisodesOfSeason($showId, $season)
  {
    $response = $this->request('/GetEpisodesForSeason/' . $showId . '/' . $season);
    if ( $response->response->status == 'false' )
      return null;
    $episodes = $response->response->results->result;

    if ( count($episodes) <= 0 )
      return null;

    $episodeList = [];
    foreach ( $episodes as $episode )
      $episodeList[] = $this->formatEpisode($episode);

    return $episodeList;
  }

  /**
   * Search episodes by a Bierdopje showId
   *
   * @param $showId
   *
   * @return array|null
   * @throws \Exception
   */
  public function getEpisodesOfShow($showId)
  {
    $response = $this->request('/GetAllEpisodesForShow/' . $showId);
    if ( $response->response->status == 'false' )
      return null;
    $episodes = $response->response->results->result;

    if ( count($episodes) <= 0 )
      return null;

    $episodeList = [];
    foreach ( $episodes as $episode )
      $episodeList[] = $this->formatEpisode($episode);

    return $episodeList;
  }

  /**
   * Search an episode by its Bierdopje Id
   *
   * @param $episodeId
   *
   * @return \stdClass
   * @throws \Exception
   */
  public function getEpisodeById($episodeId)
  {
    $response = $this->request('/GetEpisodeById/' . $episodeId);
    if ( $response->response->status == 'false' )
      return null;
    if ($response->response->cached == 'false') {
      $episode  = $response->response;
    } else {
      $episode  = $response->response->results;
    }
    $episode  = $this->formatEpisode($episode);

    return $episode;
  }

  /**
   * Format an Episode response
   *
   * @param $original
   *
   * @return \stdClass
   */
  private function formatEpisode($original)
  {
    $show = new \stdClass();

    $show->id          = (int) $original->episodeid;
    $show->tvdbId      = (int) $original->tvdbid;
    $show->title       = (string) $original->title;
    $show->showlink    = (string) $original->showlink;
    $show->episodelink = (string) $original->episodelink;
    $show->airDate     = strlen($original->airdate)
      ? Carbon::createFromFormat("d-m-Y", (string) $original->airdate)
      : null;
    $show->season      = (int) $original->season;
    $show->episode     = (int) $original->episode;
    $show->epNumber    = (int) $original->epnumber;
    $show->score       = (float) str_replace(',', '.', $original->score);
    $show->votes       = (int) $original->votes;
    $show->formatted   = (string) $original->formatted;
    $show->is_special  = ((string) $original->is_special) === "true";
    $show->summary     = (string) $original->summary;

    return $show;
  }

  /**
   * Format a Show response
   *
   * @param $original
   *
   * @return \stdClass
   */
  private function formatShow($original)
  {
    $show = new \stdClass();

    $show->id          = (int) $original->showid;
    $show->tvdbId      = (int) $original->tvdbid;
    $show->name        = (string) $original->showname;
    $show->link        = (string) $original->showlink;
    $show->firstAired  = strlen($original->firstaired)
      ? Carbon::createFromFormat('Y-m-d', (string) $original->firstaired)
      : null;
    $show->lastAired   = strlen($original->lastaired)
      ? Carbon::createFromFormat('Y-m-d', (string) $original->lastaired)
      : null;
    $show->nextEpisode = strlen($original->nextepisode)
      ? Carbon::createFromFormat('Y-m-d', (string) $original->nextepisode)
      : null;
    $show->seasons     = (int) $original->seasons;
    $show->episodes    = (int) $original->episodes;
    $show->genres      = $original->genres->result;
    $show->score       = (float) str_replace(',', '.', $original->score);
    $show->runtime     = (float) str_replace(',', '.', $original->runtime);
    $show->favorites   = (int) $original->favorites;
    $show->showstatus  = (string) $original->showstatus;
    $show->airtime     = (string) $original->airtime;
    $show->summary     = (string) $original->summary;
    $genres = [];
    foreach ( $show->genres as $key => $genre ) {
      $genre    = (string) $genre;
      $genre    = ucfirst($genre);
      $genres[] = $genre;
    }
    $show->genres = $genres;

    return $show;
  }

  /**
   * Make an HTTP request and format the XML resposne
   *
   * @param $path
   *
   * @return \SimpleXMLElement
   * @throws \Exception
   */
  protected function request($path)
  {
    $response = $this->client->get($this->url . $path);

    if ( $response->getStatusCode() != 200 )
      throw new \Exception('Bierdopje.com not available');

    $response = $this->xmlToObj($response->getBody());

    return $response;
  }

  /**
   * Convert XML string to object
   *
   * @param $fileContents
   *
   * @return \SimpleXMLElement
   */
  private function xmlToObj($fileContents)
  {
    $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
    $fileContents = trim(str_replace('"', "'", $fileContents));
    $simpleXml    = simplexml_load_string($fileContents, null, LIBXML_NOCDATA);

    return $simpleXml;
  }
}

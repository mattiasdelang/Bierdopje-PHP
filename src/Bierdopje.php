<?php

namespace mattiasdelang;

use Carbon\Carbon;
use GuzzleHttp\Client;

class Bierdopje
{

  /**
   * Bierdopje constructor.
   */
  protected $url = 'http://api.bierdopje.com/';

  /**
   * @var Client client
   */
  protected $client;

  public function __construct(Client $client)
  {
    $this->client = $client;
    $this->url .= getenv('BD_APIKEY');
  }

  public function getShowByName($showName)
  {
    $response = $this->request('/FindShowByName/' . $showName);

    $shows = $response->response->results->result;
    if (count($shows) <= 0)
      return null;

    $show = $shows[0];
    $show = $this->formatShow($show);

    return $show;
  }

  public function getShowByTvdbId($id)
  {
    $response = $this->request('/GetShowByTVDBID/' . $id);

    $show = $this->formatShow($response->response);

    return $show;


  }

  public function getEpisodesOfSeason($id, $season)
  {
    $response = $this->request('/GetEpisodesForSeason/' . $id . '/' . $season);

    $episodes = $response->response->results->result;
    if (count($episodes) <= 0)
      return null;

    $episodeList = [];
    foreach ($episodes as $episode)
      $episodeList[] = $this->formatEpisode($episode);

    return $episodeList;
  }

  public function getEpisodesOfShow($id)
  {
    $response = $this->request('/GetAllEpisodesForShow/' . $id);

    $episodes = $response->response->results->result;
    if (count($episodes) <= 0)
      return null;

    $episodeList = [];
    foreach ($episodes as $episode)
      $episodeList[] = $this->formatEpisode($episode);

    return $episodeList;
  }

  public function getEpisodeById($id)
  {
    $response = $this->request('/GetEpisodeById/' . $id);
    $episode = $response->response->results;
    $episode = $this->formatEpisode($episode);

    return $episode;

  }

  private function formatEpisode($original)
  {
    $show = new \stdClass();

    $show->id = (int)$original->episodeid;
    $show->tvdbId = (int)$original->tvdbid;
    $show->title = (string)$original->title;
    $show->showlink = (string)$original->showlink;
    $show->episodelink = (string)$original->episodelink;
    $show->airDate = Carbon::createFromFormat("d-m-Y", (string)$original->airdate);
    $show->season = (int)$original->season;
    $show->episode = (int)$original->episode;
    $show->epNumber = (int)$original->genres->epnumber;
    $show->score = (float)str_replace(',', '.', $original->score);
    $show->votes = (int)$original->votes;
    $show->formatted = (string)$original->formatted;
    $show->is_special = ((string)$original->is_special) === "true";
    $show->summary = (string)$original->summary;

    return $show;
  }

  private function formatShow($original)
  {
    $show = new \stdClass();

    $show->id = (int)$original->showid;
    $show->tvdbId = (int)$original->tvdbid;
    $show->name = (string)$original->showname;
    $show->link = (string)$original->showlink;
    $show->firstAired = Carbon::createFromFormat('Y-m-d', (string)$original->firstaired);
    $show->lastAired = Carbon::createFromFormat('Y-m-d', (string)$original->lastaired);
    $show->nextEpisode = Carbon::createFromFormat('Y-m-d', (string)$original->nextepisode);
    $show->seasons = (int)$original->seasons;
    $show->episodes = (int)$original->episodes;
    $show->genres = $original->genres->result;
    $show->score = (float)str_replace(',', '.', $original->score);
    $show->runtime = (float)str_replace(',', '.', $original->runtime);
    $show->favorites = (int)$original->favorites;
    $show->showstatus = (string)$original->showstatus;
    $show->airtime = (string)$original->airtime;
    $show->summary = (string)$original->summary;


    $genres = [];
    foreach ($show->genres as $key => $genre) {
      $genre = (string)$genre;
      $genre = ucfirst($genre);
      $genres[] = $genre;
    }
    $show->genres = $genres;

    return $show;
  }

  /**
   * @param $showName
   * @return \Psr\Http\Message\ResponseInterface|\SimpleXMLElement
   * @throws \Exception
   */
  protected function request($path)
  {
    $response = $this->client->get($this->url . $path);
    if ($response->getStatusCode() != 200)
      throw new \Exception('Bierdopje.com not available');

    $response = $this->xmlToObj($response->getBody());
    return $response;
  }

  /**
   * Convert XML string to object
   * @param $fileContents
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

<?php

use mattiasdelang\Bierdopje as Api;

class Bierdopje extends PHPUnit_Framework_TestCase
{

  /**
   * @var \mattiasdelang\Bierdopje
   */
  private $api;


  protected function setUp()
  {
    $httpClient = new \GuzzleHttp\Client();
    $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

    $bierdopje = new Api($httpClient, $serializer);
    $this->api = $bierdopje;
  }

  /**
   * @vcr bierdopje.yml
   */
  public function test_it_fetches_shows_by_name()
  {
    $show = $this->api->getShowByName('Arrow');

    $this->assertEquals($show->id, 16290);
    $this->assertEquals($show->tvdbId, 257655);
    $this->assertEquals($show->name, "Arrow");
    $this->assertEquals($show->firstAired->format('d-m-Y'), '10-10-2012');
    #$this->assertEquals($show->lastAired->format('d-m-Y'), '18-11-2015');
    #$this->assertEquals($show->nextEpisode->format('d-m-Y'), '27-01-2016');
    $this->assertTrue($show->seasons >= 4, 'There are more than 4 seasons');
    $this->assertTrue($show->episodes >= 93, 'There are more than 93 episodes');

    $this->assertTrue(in_array('Action', $show->genres), 'Genres contains "Action"');
    $this->assertTrue(in_array('Adventure', $show->genres), 'Genres contains "Adventure"');
    $this->assertTrue(in_array('Crime', $show->genres), 'Genres contains "Crime"');

    $this->assertTrue(strlen($show->summary) > 100, 'The summary has more than 100 chars');
  }

  /**
   * @vcr bierdopje.yml
   */
  public function test_it_fetches_shows_by_tvdbId()
  {

    $show = $this->api->getShowBytvdbId(257655);

    $this->assertEquals($show->id, 16290);
    $this->assertEquals($show->tvdbId, 257655);
    $this->assertEquals($show->name, "Arrow");
    $this->assertEquals($show->firstAired->format('d-m-Y'), '10-10-2012');
    #$this->assertEquals($show->lastAired->format('d-m-Y'), '18-11-2015');
    #$this->assertEquals($show->nextEpisode->format('d-m-Y'), '27-01-2016');
    $this->assertTrue($show->seasons >= 4);
    $this->assertTrue($show->episodes >= 93);

    $this->assertTrue(in_array('Action', $show->genres));
    $this->assertTrue(in_array('Adventure', $show->genres));
    $this->assertTrue(in_array('Crime', $show->genres));

    $this->assertTrue(strlen($show->summary) > 100);
  }

  /**
   * @vcr bierdopje.yml
   */
  public function test_it_fetches_all_episodes_of_a_season_for_show()
  {
    $episodes = $this->api->getEpisodesOfSeason(16290, 2);

    $this->assertEquals(count($episodes), 24);

    $episode = $episodes[0];

    $this->assertEquals($episode->id, 772542);
    $this->assertEquals($episode->tvdbId, 4599381);
    $this->assertEquals($episode->title, "City of Heroes");
    $this->assertEquals($episode->season, 2);
    $this->assertEquals($episode->episode, 1);
    $this->assertEquals($episode->airDate->format('d-m-Y'), '09-10-2013');
    $this->assertFalse($episode->is_special, 'Is not a special episode');
    $this->assertEquals($episode->formatted, 'S02E01');
    $this->assertTrue(strlen($episode->summary) > 100, "summary has more than 100 characters");
  }

  /**
   * @vcr bierdopje.yml
   */
  public function test_it_fetches_all_episodes_of_a_show()
  {
    $episodes = $this->api->getEpisodesOfShow(16290);

    $this->assertTrue(count($episodes) >= 93);
    $episode = $episodes[0];

    $this->assertEquals($episode->id, 814124);
    $this->assertEquals($episode->tvdbId, 4659835);
    $this->assertEquals($episode->title, "Year One");
    $this->assertEquals($episode->season, 0);
    $this->assertEquals($episode->episode, 1);
    $this->assertEquals($episode->airDate->format('d-m-Y'), '02-10-2013');
    $this->assertTrue($episode->is_special, 'Is not a special episode');
    $this->assertEquals($episode->formatted, 'S00E01');
    $this->assertTrue(strlen($episode->summary) > 100, "summary has more than 100 characters");
  }

  /**
   * @vcr bierdopje.yml
   */
  public function test_it_fetches_an_episode_by_id()
  {
    $episode = $this->api->getEpisodeById(814124);

    $this->assertEquals($episode->id, 814124);
    $this->assertEquals($episode->tvdbId, 4659835);
    $this->assertEquals($episode->title, "Year One");
    $this->assertEquals($episode->season, 0);
    $this->assertEquals($episode->episode, 1);
    $this->assertEquals($episode->airDate->format('d-m-Y'), '02-10-2013');
    $this->assertTrue($episode->is_special, 'Is not a special episode');
    $this->assertEquals($episode->formatted, 'S00E01');
    $this->assertTrue(strlen($episode->summary) > 100, "summary has more than 100 characters");
  }


}




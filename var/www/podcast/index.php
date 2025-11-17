<?php
/*
    @license    http://www.gnu.org/licenses/agpl.txt
    @copyright  2014 Sourcefabric o.p.s.
    @link       http://www.sourcefabric.org
    @author     Micz Flor <micz.flor@sourcefabric.org>
    
    This php script will create a podcast XML on the fly
    listing all mp3 files in the same directory.
*/


$channeltitle   = file_get_contents('podcastName');
$channelauthor  = "Thomas Quander";
/* $sortby sets the order in which tracks are listed. 
   Options: 
   "newest" = newest on top
   "oldest" = oldest on top
   "filedesc" = alphabetically descending
   "fileasc" = alphabetically ascending
   default: "filedesc" (== how streamplan.sh works)
*/
$sortby = "filedesc"; 

$dir = "http://recorder.athome.anja-und-thomas.de";
$parts = explode('/',$_SERVER['REQUEST_URI']);
for ($i = 0; $i < count($parts) - 1; $i++) {
  $dir .= $parts[$i] . "/";
}
header('Content-type: text/xml', true);

print"<?xml version='1.0' encoding='UTF-8'?>
<rss xmlns:itunes='http://www.itunes.com/DTDs/Podcast-1.0.dtd' version='2.0'>
<channel>
  <title>$channeltitle</title>
  <link>$dir</link>
  <itunes:author>$channelauthor</itunes:author>
  <image>
    <url>$dir/podcast.jpg</url>
    <title>$channeltitle</title>
    <link>$dir</link>
  </image>
";
/**/
// read all mp3 files in the directory
$temp = glob("{*.mp3,*.m4a,*.aac}", GLOB_BRACE);
// create array with timestamp.filename as key
foreach ($temp as $filename) {
  $mp3files[filemtime($filename).$filename] = $filename;
}
// change the order of the list according to $sortby set above
switch ($sortby) {
  case "newest":
    krsort($mp3files);
    break;
  case "oldest":
    ksort($mp3files);
    break;
  case "fileasc":
    natcasesort($mp3files);
    break;
  default:
    // filedesc 
    natcasesort($mp3files);
    $mp3files = array_reverse($mp3files);
    break;
}
// go through files and create <item> for podcast
foreach ($mp3files as $filename) {
  // set empty array for metadata
  $iteminfo = array(
    "artist" => "",
    "title" => "",
    "album" => "",
    "length" => ""
  );

  $filetype = substr($filename, -3);
  $audiotype = "";
  switch($filetype) {
    case "mp3":
      $audiotype = "audio/mpeg";
      break;
    case "m4a":
    case "aac":
      $audiotype = "audio/aac";
      break;
    default:
      $audiotype = "audio/mpeg";
      break;
  }

  if (file_exists(substr($filename, 0, -3) . "metadata")) {
    $trackinfo = explode("\n", file_get_contents(substr($filename, 0, -3) . "metadata"));
    $iteminfo['length'] = $trackinfo[3];
    $iteminfo['artist'] = $trackinfo[0];
    $iteminfo['title'] = $trackinfo[1];
    $iteminfo['album'] = $trackinfo[2];
  } else {
    $iteminfo['length'] = round(shell_exec("ffprobe -i '$filename' -show_entries format=duration -v quiet -of csv='p=0'"));
    $iteminfo['artist'] = shell_exec("ffprobe -loglevel error -show_entries format_tags=artist -of default=noprint_wrappers=1:nokey=1 '$filename'");
    $iteminfo['title'] = shell_exec("ffprobe -loglevel error -show_entries format_tags=title -of default=noprint_wrappers=1:nokey=1 '$filename'");
    $iteminfo['album'] = shell_exec("ffprobe -loglevel error -show_entries format_tags=album -of default=noprint_wrappers=1:nokey=1 '$filename'");
    file_put_contents(substr($filename, 0, -3) . "metadata", $iteminfo);
  }

  print "
  <item>
    <title>".$iteminfo['artist']." - ".$iteminfo['title']."</title>
    <itunes:author>".$iteminfo['artist']."</itunes:author>
    <itunes:subtitle>".$iteminfo['album']."</itunes:subtitle>
    <description>".$iteminfo['title']." by ".$iteminfo['artist']." Recorded on ".date ("r", filemtime($filename))."</description>
    <enclosure url=\"".$dir.$filename."\" length=\"".$iteminfo['length']."\" type=\"$audiotype\"/>
    <guid>".$dir.$filename."</guid>
    <pubDate>".date ("r", filemtime($filename))."</pubDate>
  </item>";
}
print"
</channel>
</rss>";
?>

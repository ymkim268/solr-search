<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;


$sortby = null;
if(isset($_REQUEST['sortby'])) {
  $sortby = $_REQUEST['sortby'];
}

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('solr-php-client/Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    if(strcmp($sortby, "lucene") == 0) {
      $additionalParameters = array(
      'fl' => array(
        'id', 
        'title', 
        'og_url', 
        'description',
        'og_description'
      ),
      'wt' => 'json');
    } else {
      $additionalParameters = array(
      'sort' => 'pageRankFile desc',  
      'fl' => array(
        'id', 
        'title', 
        'og_url', 
        'description',
        'og_description'
      ),
      'wt' => 'json');
    }
    
    $results = $solr->search($query, 0, $limit, $additionalParameters);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>


<style type="text/css">

a:link {
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

</style>

<html>
  <head>
    <title>HW4 - Solr - Fox News</title>
  </head>
  <body>
    <div style="text-align: center">
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <br/><br/>
      Sort:
      <input type="radio" name="sortby" value="lucene"  
        <?php 
        if ($query && (strcmp($sortby, "lucene") == 0))
        {
          echo "checked";
        } else if (!$query && ($sortby == NULL)) 
        {
          echo "checked";
        }?>> Lucene(Default)
      <input type="radio" name="sortby" value="pagerank"
        <?php 
        if ($query && (strcmp($sortby, "pagerank") == 0))
        {
          echo "checked";
        } ?>> PageRank
      <br/><br/>
      <input type="submit"/>
    </form>
  </div>

<?php

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>


<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
      <li>
        <table style="border: 0px solid black; text-align: left">

          <tr>
            <!-- Title -->
            <td style="font-size: 130%;"><?php 
            $url_str = htmlspecialchars($doc->og_url, ENT_NOQUOTES, 'utf-8');
            $title_str = htmlspecialchars($doc->title, ENT_NOQUOTES, 'utf-8');
            echo "<a href=\"" . $url_str . "\">" . $title_str . "</a>"; ?></td>
          </tr>

          <tr>
            <!-- URL -->
            <td style="font-size: 90%;"><?php 
            $url_str = htmlspecialchars($doc->og_url, ENT_NOQUOTES, 'utf-8');
            echo "<a href=\"" . $url_str . "\" style=\"color: green\">" . $url_str . "</a>"; ?></td>
          </tr>

          <tr>
            <!-- ID -->
            <td style="font-size: 90%;"><?php echo htmlspecialchars($doc->id, ENT_NOQUOTES, 'utf-8'); ?></td>
          </tr>

          <tr>
            <!-- Description -->
            <td style="font-size: 90%;"><?php 
            if (($doc->description == null) && ($doc->og_description == null)) {
              echo "N/A";
            } else {
              if(($doc->og_description != null)) {
                echo htmlspecialchars($doc->og_description, ENT_NOQUOTES, 'utf-8');
                
              } else {
                if(is_array($doc->description)) {
                  echo htmlspecialchars($doc->description[0], ENT_NOQUOTES, 'utf-8');
                } else {
                  echo htmlspecialchars($doc->description, ENT_NOQUOTES, 'utf-8');
                }
              }
            } ?></td>
          </tr>
        </table>
      </li>
      <br />
<?php
  }
?>

    </ol>
<?php
}
?>
  </body>
</html>
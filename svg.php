<?php
$text = isset($_GET['t']) ? $_GET['t'] : 'YOURS';
$size = round(strlen($text) * 25);
header('Content-Type: image/svg+xml');
?>
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   version="1.1"
   width="<?php echo $size; ?>"
   height="<?php echo $size/2; ?>"
   id="svg2">
  <defs
     id="p1n<?php echo $size; ?>" />
  <metadata
     id="metadata7">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
        <dc:title></dc:title>
      </cc:Work>
    </rdf:RDF>
  </metadata>
  <text
     x="10"
     y="<?php echo $size/2/2; ?>"
     id="maintext<?php echo $size; ?>"
     xml:space="preserve"
     style="font-size:2.2em;font-style:bold;font-weight:bold;line-height:100%;letter-spacing:0px;word-spacing:0px;fill:#000000;fill-opacity:1;stroke:none;font-family:Arial, Helvetica"><tspan
       x="10"
       y="<?php echo $size/2/2; ?>"
       id="tspan<?php echo $size; ?>"
       style="fill:#000000"><?php echo $text; ?></tspan></text>
</svg>
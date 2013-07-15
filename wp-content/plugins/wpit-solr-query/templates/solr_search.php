<?php
/*
Template Name: Search
*/
?>

<?php get_header(); ?>
<div id="content">

<?php
	$solr = wpit_solr_query_search_results();


//TODO: DEBUG da rimuovere  TEMP
echo '<pre>
' . print_r($solr->solr_get_total_posts_count(), 1) . '</pre>';



    while ( $solr->solr_have_posts() ) : $solr->solr_the_post(); ?>

    	<h2><a href="<?php $solr->solr_permalink(); ?>"><?php $solr->solr_the_title(); ?></a></h2>
    	<p>
    	<?php $solr->solr_the_content(array('words'=>35, 'end'=>'[..]')); ?>
		</p>

<?php
			endwhile;

?>

</div>

<?php get_footer(); ?>

<?php

class pluginBluSeo extends Plugin {

	public function init()
	{
		// Fields and default values for the database of this plugin
		$this->dbFields = array(
			'defaultImage'=>'',
			'fbpage'=>'',
			'description'=>''
		);
	}

	public function form()
	{
		global $Language;

		$html  = '<div>';
		$html .= '<label>' . $Language->get('default-image') . '</label>';
		$html .= '<input id="jsdefaultImage" name="defaultImage" type="text" value="' . $this->getValue('defaultImage') . '" placeholder="https://">';
		$html .= '</div>';
		
		$html .= '<div>';
		$html .= '<label>' . $Language->get('description') . '</label>';
		$html .= '<input id="jsdescription" name="description" type="text" value="' . $this->getValue('description') . '" />';
		$html .= '</div>';
		
		$html .= '<div>';
		$html .= '<label>' . $Language->get('fbpage') . '</label>';
		$html .= '<input id="jsfbpage" name="fbpage" type="text" value="' . $this->getValue('fbpage') . '" />';
		$html .= '</div>';

		return $html;
	}

	public function siteHead()
	{
		global $Url;
		global $Site;
		global $WHERE_AM_I;
		global $pages;
		global $page;
		
		//Default Values
		$og = array(
		'lang'		=> $Site->language(),
		'noindex'	=> false
		);
		
		if( $WHERE_AM_I == 'page' ) {
			
			$og['type']			= 'article';
			$og['title']		= $page->title() . ' | ' . $Site->title();
			$og['description']	= substr( strip_tags( $page->description() ), 0, 150 );
			$og['url']			= $page->permalink($absolute=true);
			$og['image'] 		= $page->coverImage($absolute=true);
			
			if ( strpos( $page->content(), 'noindex' ) !== false )
			{
				$og['noindex']		= true;				
			}
			
		} elseif( $WHERE_AM_I == 'category' ) {
			
			$og['type']			= 'website';
			$og['title']		= $Site->slogan() . ' | ' . $Site->title();
			$og['description']	= ( Text::isNotEmpty( $this->getValue( 'description' ) ) ) ? $this->getValue( 'description' ) : $Site->description();
			$og['image']		= $this->getValue( 'defaultImage' );
			$og['url']			= $Site->url();
			$og['noindex']		= true;
			
		} elseif( $WHERE_AM_I == 'tag' ) {
			
			$og['type']			= 'website';
			$og['title']		= $Site->slogan() . ' | ' . $Site->title();
			$og['description']	= ( Text::isNotEmpty( $this->getValue( 'description' ) ) ) ? $this->getValue( 'description' ) : $Site->description();
			$og['image']		= $this->getValue( 'defaultImage' );
			$og['url']			= $Site->url();
			$og['noindex']		= true;
			
		} else {
			
			$og['type']			= 'website';
			$og['title']		= $Site->slogan() . ' | ' . $Site->title();
			$og['description']	= ( Text::isNotEmpty( $this->getValue( 'description' ) ) ) ? $this->getValue( 'description' ) : $Site->description();
			$og['image']		= $this->getValue('defaultImage');
			$og['url']			= $Site->url();
			
			if ( strpos( $_SERVER['REQUEST_URI'], 'page=' ) !== false )
			{
				$og['noindex']		= true;				
			}

		}
		
		$html  = PHP_EOL . '<!-- This site is optimized with the BluSEO plugin - https://g3ar.xyz/projects/bluseo-seo-plugin-bludit/ -->' . PHP_EOL;
		
		if ( $og['noindex'] )
			$html .= '<meta name="robots" content="noindex,follow"/>' . PHP_EOL;
		
		$html .= '<link rel="canonical" href="' . $og['url'] . '" />' . PHP_EOL;
		$html .= '<meta property="og:locale" content="' . $Site->locale() . '" />' . PHP_EOL;
		$html .= '<meta property="og:type" content="' . $og['type'] . '" />' . PHP_EOL;
		$html .= '<meta property="og:title" content="' . htmlspecialchars( $og['title'], ENT_QUOTES ) . '" />' . PHP_EOL;
		$html .= '<meta property="og:description" content="' . htmlspecialchars( $og['description'], ENT_QUOTES ) . '" />' . PHP_EOL;
		$html .= '<meta property="og:url" content="' . $og['url'] . '" />' . PHP_EOL;
		$html .= '<meta property="og:site_name" content="' . htmlspecialchars( $Site->title(), ENT_QUOTES ) . '" />' . PHP_EOL;
		
		if( $WHERE_AM_I == 'page' ) {
			
			if ( Text::isNotEmpty( $this->getValue('fbpage') ) )
				$html .= '<meta property="article:publisher" content="' . $this->getValue('fbpage') . '" />' . PHP_EOL;
			
			$cat = $page->categoryMap(true);
			
			$tags = $page->tags(true);
				if ( !empty($tags) ) {
					foreach($tags as $tagKey=>$tagName)
						$html .= '<meta property="article:tag" content="' . htmlspecialchars( $tagName, ENT_QUOTES ) . '" />' . PHP_EOL;
				}
			
			if ( Text::isNotEmpty($cat) ) {
				
				$html .= '<meta property="article:section" content="' . ucwords( str_replace( '-', ' ', $cat ) ) . '" />' . PHP_EOL;
			}
			
			$html .= '<meta property="article:published_time" content="' . date ('c', strtotime($page->dateRaw())) . '" />' . PHP_EOL;
			
			if ( Text::isNotEmpty($page->dateModified()) ) {
				$html .= '<meta property="article:modified_time" content="' . date ('c', strtotime($page->dateModified())) . '" />' . PHP_EOL;
				$html .= '<meta property="og:updated_time" content="' . date ('c', strtotime($page->dateModified())) . '" />' . PHP_EOL;
			}
			
			if (!empty($og['image'])) {
				
				list($img_width, $img_height) = @getimagesize($og['image']);
				
				if ( !empty($img_width) && !empty($img_height) ) {
					$html .= '<meta property="og:image:width" content="' . $img_width . '" />' . PHP_EOL;
					$html .= '<meta property="og:image:height" content="' . $img_height . '" />' . PHP_EOL;
				}
				
			} else {
				
				$src = $this->getImage( $page->content() );
					if ( $src!==false )
						$og['image'] = $src;
					else {
					if ( Text::isNotEmpty($this->getValue('defaultImage')) )
						$og['image'] = $this->getValue( 'defaultImage' );
				}
			}
				
		}
		
		$html .= '<meta property="og:image" content="' . $og['image'] . '" />' . PHP_EOL;
		
		if ( substr( $og['image'], 0, 8 ) == "https://" )
			$html .= '<meta property="og:image:secure_url" content="' . $og['image'] . '" />' . PHP_EOL;
		
		$html .= '<meta name="twitter:card" content="summary_large_image" />' . PHP_EOL;
		$html .= '<meta name="twitter:description" content="' . htmlspecialchars( $og['description'], ENT_QUOTES ) . '" />' . PHP_EOL;
		$html .= '<meta name="twitter:title" content="' . htmlspecialchars( $og['title'], ENT_QUOTES ) . '" />' . PHP_EOL;
		$html .= '<meta name="twitter:image" content="' . $og['image'] . '" />' . PHP_EOL;
		$html .= '<!-- / BluSEO plugin. -->' . PHP_EOL;

		return $html;
	}

	// Returns the first image from the page content
	private function getImage($content)
	{
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/ii', $content, $matches);
		
		if (!empty($matches[1][0]))
			return $matches[1][0];

		return false;
	}
	
	public function beforeAll()
	{
		global $Site;
						
		if( $this->webhook( 'robots.txt' ) ) {
			
			//Support Blumap and Sitemap Plugins
			$blumap_xml = PATH_ROOT.'bl-content'.DS.'databases'.DS.'plugins'.DS.'blumap'.DS.'sitemap.xml';
			$sitemap_xml = PATH_ROOT.'bl-content'.DS.'databases'.DS.'plugins'.DS.'sitemap'.DS.'sitemap.xml';
			
			// Make it a plain text file
			header('Content-Type:text/plain');

			// Build data
			$html = 'User-agent: *' . PHP_EOL;
			$html .= 'Disallow: /bl-content' . PHP_EOL;
			$html .= 'Disallow: /bl-kernel' . PHP_EOL;
			$html .= 'Disallow: /bl-languages' . PHP_EOL;
			$html .= 'Disallow: /bl-plugins' . PHP_EOL;
			$html .= 'Disallow: /bl-themes' . PHP_EOL;
			$html .= 'Disallow: /README.md' . PHP_EOL;
			$html .= 'Allow: /*.js' . PHP_EOL;
			$html .= 'Allow: /*.css' . PHP_EOL;
			$html .= 'Host: ' . $Site->url() . PHP_EOL;
						
			if ( file_exists( $blumap_xml ) || file_exists( $sitemap_xml ) )
				$html .= 'Sitemap: ' . $Site->url() . '/sitemap.xml' . PHP_EOL;
			
			echo $html;

			// Terminate the run successfully
			exit(0);
		}
	}
}

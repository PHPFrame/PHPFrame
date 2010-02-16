<?php
/**
 * PHPFrame/Document/HTMLRenderer.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/PHPFrame
 */

/**
 * HTML Renderer Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/PHPFrame
 * @see      PHPFrame_IRenderer
 * @since    1.0
 */
class PHPFrame_HTMLRenderer implements PHPFrame_IRenderer
{
    /**
     * Full path to directory with HTML view files.
     * 
     * @var string
     */
    private $_views_path;
    
    /**
     * Constructor.
     * 
     * @param string $views_path Full path to directory with HTML view files.
     * 
     * @return void
     * @since  1.0
     */
    public function __construct($views_path)
    {
        $this->_views_path = (string) $views_path;
    }
    
    /**
     * Render a given value.
     * 
     * @param mixed $value The value we want to render.
     * 
     * @return void
     * @since  1.0
     * @see    PHPFrame/Document/PHPFrame_IRenderer#render($value)
     */
    public function render($value)
    {
        $str = $value;
        
        if ($value instanceof PHPFrame_View) {
            $str = $this->renderView($value);
        }
        
        return $str;
    }
    
    /**
     * Render view and store in document's body
     * 
     * @param PHPFrame_View $view The view object to process.
     * 
     * @return void
     * @since  1.0
     */
    public function renderView(PHPFrame_View $view) 
    {
        $tmpl_path = $this->_views_path.DS.$view->getName().".php";
        
        if (is_file($tmpl_path)) {
            // Start buffering
            ob_start();
            // set view data as local array
            $data = $view->getData();
            
            if (is_array($data) && count($data) > 0) {
                foreach ($data as $key=>$value) {
                    $$key = $value;
                }
            }
            
            // Include template file
            include_once $tmpl_path;
            // save buffer in body property
            $str = ob_get_contents();
            // clean output buffer
            ob_end_clean();
        } else {
            $msg = "Layout template file ".$tmpl_path." not found.";
            throw new RuntimeException($msg);
        }
        
        return $str;
    }
    
    /**
     * Render a partial view
     * 
     * @param string $name The partial name.
     * @param array  $data An associative array with data to be passed to the 
     *                     partial view.
     * 
     * @return void
     * @since  1.0
     */
    public function renderPartial($name, array $data=null)
    {
        $name = trim((string) $name);
        $path = $this->_views_path.DS."partials".DS.$name;
        
        if (!is_file($path)) {
            $path .= ".php";
            if (!is_file($path)) {
                return;
            }
        }
        
        // Start buffering
        ob_start();
        
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key=>$value) {
                $$key = $value;
            }
        }
            
        // Include partial file
        include $path;
        // save buffer in body property
        $partial = ob_get_contents();
        // clean output buffer
        ob_end_clean();
        
        return $partial;
    }
    
    /**
     * Render pathway object.
     * 
     * @param PHPFrame_Pathway $pathway Reference to the pathway object we want 
     *                                  to render.
     *                                  
     * @return string
     * @since  1.0
     */
    public function renderPathway(PHPFrame_Pathway $pathway)
    {
        $array = $pathway->toArray();
        
        $html = '<div class="pathway">';
        
        for ($i=0; $i<count($array); $i++) {
            if ($i>0) {
                $html .= ' &gt;&gt; ';
            }
            $html .= '<span class="pathway_item">';
            if (!empty($array[$i]['url']) && $i < (count($array))-1) {
                $html .= '<a href="'.$array[$i]['url'].'">';
                $html .= $array[$i]['title'].'</a>';
            } else {
                $html .= $array[$i]['title'];
            }
            $html .= '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Method used to render Collections in HTML format.
     * 
     * @param PHPFrame_Collection $collection Collection object to render.
     * @param array               $headings   [Optional] An array with the 
     *                                        column headings. If no specified 
     *                                        headings we get keys from 
     *                                        collection.
     * 
     * @return string
     * @since  1.0
     */
    public function renderCollection(
        PHPFrame_Collection $collection, 
        $headings=null
    ) {
        // Build table to display row data
        $html = "<table class=\"data_list\" width=\"100%\">\n";
        
        // Prepare heading array
        if (!is_null($headings) && !is_array($headings)) {
            $msg = "Wrong data type.";
            $msg .= "Headings must be passed as an array.";
            throw new RuntimeException($msg);
        } elseif (is_null($headings)) {
            // If no specified headings we get keys from collection
            $headings = $collection->getKeys();
        }
        
        // Print headings
        $html .= "<thead>\n<tr>\n";
        foreach ($headings as $heading) {
            $html .= "<th>".$heading."</th>\n";
        }
        $html .= "</tr>\n</thead>\n";
        
        // Print tbody
        $html .= "<tbody>\n";
        foreach ($collection as $row) {
            $html .= "<tr>\n";
            foreach ($row->getKeys() as $key) {
                $html .= "<td>".$row->$key."</td>\n";
            }
            $html .= "</tr>\n";
        }
        $html .= "</tbody>\n";
        $html .= "</table>";
        
        return $html;
    }
    
    /**
     * Render HTML filter for collections
     * 
     * This method builds an HTML string with UI filtering elements to be used 
     * with row collection objects.
     * 
     * @param PHPFrame_Collection $collection
     * 
     * @return string
     * @since  1.0
     */
    public function renderCollectionFilter(PHPFrame_Collection $collection)
    {
        $html = '<div class="row_collection_filter">';
        
        // Print form to select limit per page
        $html .= '<div class="subset_limit">';
        $html .= '<form name="limitform" id="limitform" method="post">';
        $html .= 'Display Num: ';
        $html .= '<select name="limit" ';
        $html .= 'onchange="document.forms[\'limitform\'].submit();">';
        for ($i=25; $i<=100; $i+=25) {
            $html .= '<option value="'.$i.'"';
            if ($collection->getLimit() == $i) {
                $html .= ' selected';
            }
            $html .= '>'.$i.'</option>';
        }
        $html .= '<option value="-1">-- All --</option>';
        $html .= '</select>';
        $html .= '</form>';
        $html .= '</div>';
        
        // Print subset info
        $html .= '<div class="subset_info">';
        $html .= ($collection->getLimitstart()+1);
        $html .= ' - '.($collection->getLimitstart() + count($collection));
        $html .= ' of '.$collection->getTotal();
        $html .= '</div>';
        
        // Print search box
        $html .= '<script language="javascript" type="text/javascript">
                    function submit_filter(reset) {
                        var form = document.forms["listsearchform"];
                        
                        if (reset){
                            form.search.value = "";
                        }
                        
                        form.submit();
                    }
                  </script>';
        
        $html .= '<form action="index.php" id="listsearchform" ';
        $html .= 'name="listsearchform" method="post">';
        $html .= '<input type="text" name="search" id="search" value="';
        $html .= PHPFrame::Request()->getParam('search').'">';
        $html .= '<button type="button" class="button" ';
        $html .= 'onclick="submit_filter(false);">Search</button>';
        $html .= '<button type="button" class="button" ';
        $html .= 'onclick="submit_filter(true);">Reset</button>';
        $html .= '<input type="hidden" name="component" value="';
        $html .= PHPFrame::Request()->getControllerName().'" />';
        $html .= '<input type="hidden" name="action" value="';
        $html .= PHPFrame::Request()->getAction().'" />';
        $html .= '</form>';
        
        $html .= '</div>';
         
        return $html;
    }
    
    /**
     * Render HTML pagination for collection object.
     * 
     * @param PHPFrame_Collection $collection The collection object for which 
     *                                        to create the pagination.
     * 
     * @return string
     * @since  1.0
     */
    public function renderPagination(PHPFrame_Collection $collection)
    {
        $html = '';
        
        if ($collection->getPages() <= 1) {
            return $html;
        }
        
        $html .= '<div class="pagination">';
        
        // Print list with prev, next and so on...
        $href = 'index.php?controller='.PHPFrame::Request()->getControllerName();
        $href .= '&amp;action='.PHPFrame::Request()->getAction();
        $href .= '&amp;limit='.$collection->getLimit();
        
        $html .= '<ul>';
        // Start link
        $html .= '<li>';
        if ($collection->getCurrentPage() != 1) {
            $html .= '<a href="'.$href.'&amp;limitstart=0">Start</a>';
        } else {
            $html .= 'Start';
        }
        $html .= '</li>';
        // Prev link
        $html .= '<li>';
        if ($collection->getCurrentPage() != 1) {
            $html .= '<a href="'.$href.'&amp;limitstart=';
            $html .= (($collection->getCurrentPage()-2) * $collection->getLimit());
            $html .= '">Prev</a>';
        } else {
            $html .= 'Prev';
        }
        $html .= '</li>';
        // Page numbers
        for ($j=0; $j<$collection->getPages(); $j++) {
            $html .= '<li>';
            if ($collection->getCurrentPage() != ($j+1)) {
                $html .= '<a href="'.$href.'&amp;limitstart=';
                $html .= ($collection->getLimit() * $j).'">'.($j+1).'</a>';    
            } else {
                $html .= ($j+1);
            }
            $html .= '</li>';
        }
        // Next link
        $html .= '<li>';
        if ($collection->getCurrentPage() != $collection->getPages()) {
            $html .= '<a href="'.$href.'&amp;limitstart=';
            $html .= ($collection->getCurrentPage() * $collection->getLimit());
            $html .= '">Next</a>';    
        } else {
            $html .= 'Next';
        }
        // End link
        $html .= '<li>';
        if ($collection->getCurrentPage() != $collection->getPages()) {
            $html .= '<a href="'.$href.'&amp;limitstart=';
            $html .= (($collection->getPages()-1) * $collection->getLimit());
            $html .= '">End</a>';    
        } else {
            $html .= 'End';
        }
        $html .= '</li>';
        $html .= '</ul>';
        
        // Print page info
        $html .= 'Page '.$collection->getCurrentPage();
        $html .= ' of '.$collection->getPages();
        
        $html .= "</div>";
        
        return $html;
    }
}

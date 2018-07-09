<?php
namespace Vendor;

class MyPaging 
{
    protected $count = 0;  //总数
    protected $currPage = 1; //当前页
    protected $pageSize = 20 ; //每页条数
    protected $maxPage = 0; //最大页数
    protected $pageHTML = '';

    public function __construct ($count = 0, $currPage = 1, $urlTemplate = '', $pageSize = 20)
    {
        $this->count = $count;
        $this->currPage = $currPage;
        $urlTemplate && $this->urlTemplate = $urlTemplate;
        $pageSize && $this->pageSize = $pageSize;
    }

    public function show ()
    {
        $this->maxPage = ceil($this->count / $this->pageSize);
        if ($this->maxPage < 2) return $this->pageHTML;
        $this->currPage = $this->currPage < 1 ? 1 : $this->currPage;
        $this->currPage = $this->currPage > $this->maxPage ? $this->maxPage : $this->currPage;

        $this->pageHTML = '<ul class="am-pagination tpl-pagination">';
        //前一页
        if ($this->currPage != 1) {
            $this->pageHTML .= $this->getLinkButton($this->currPage - 1, '«');
        } else {
            $this->pageHTML .= $this->getLinkButton($this->currPage, '«', true);
        }

        //前几页
        $this->pageHTML .= $this->preLinks($this->currPage);
        //当前页
        $this->pageHTML .= $this->getLinkButton($this->currPage);
        //后几页
        $this->pageHTML .= $this->nextLinks($this->currPage);

        //后一页
        if ($this->currPage != $this->maxPage ) {
            $this->pageHTML .= $this->getLinkButton($this->currPage + 1, '»');
        } else {
            $this->pageHTML .= $this->getLinkButton($this->currPage, '»', true);
        }
        $this->pageHTML .= '</ul>';
        return $this->pageHTML;
    }

    private function preLinks ($page)
    {
        if ($page == 1 || $page <= $this->currPage - 4) return '';
        $prePage = $page - 1;
        return $this->preLinks($prePage) . $this->getLinkButton($prePage);
    }

    private function nextLinks ($page)
    {
        if ( $page == $this->maxPage || $page >= $this->currPage + 4 ) return '';
        $prePage = $page + 1;
        return  $this->getLinkButton($prePage) . $this->nextLinks($prePage);
    }

    private function getLinkButton ($page, $text = null, $disable = false)
    {
        if (!$text) $text = $page;
        $href = $this->getUrl($page);
        $classStr = '';

        if ($page == $this->currPage) {
            $classStr = 'am-active ';
            $href = '';
        }

        if ($disable) {
            $classStr = 'am-disabled ';
            $href = '';
        }
        return '<li class="' .  $classStr . '"><a href="' . $href  . '">' . $text . '</a></li>' ;
    } 

    private function getUrl ($page)
    {
        $urlTemplate = $this->urlTemplate ? $this->urlTemplate : $_SERVER['REQUEST_URI'];
        $url = rtrim(preg_replace("/&?page=?[\d]{0,}/", "", $urlTemplate), '?');
        $urlSymbol = strpos($url, '?') !==false ? '&' : '?';
        return $url . $urlSymbol . 'page=' . $page;
    }
}
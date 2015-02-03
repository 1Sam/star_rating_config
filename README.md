# xe/modules/star_rating_config/
별점위젯 설정 모듈


1. 본문 보기에 별점을 표시할 때
<pre>&lt;img class=&quot;zbxe_widget_output&quot; widget=&quot;star_rating&quot; /&gt;</pre>

2. 리스트, 갤러리, 카드, 웹진형태에 별점을 표시할 때
<pre>&lt;img class="zbxe_widget_output" widget="star_rating" document_srl="{$document-&gt;document_srl}"|cond="$document_srl!=$document-&gt;document_srl" /&gt;</pre>



연관글 별점 위젯 : <a href="https://github.com/1Sam/star_rating">star_rating</a> 


2015/02/03 : 글 삭제 트리거 추가 triggerDelete_star_rating_log();

<?php
/**
 * @class  star_rating_configController
 * @author  (csh@korea.com)
 * @brief  star_rating_config 모듈의 Controller class
 **/

class star_rating_configController extends star_rating_config {

	/**
	 * @brief 초기화
	 **/

	function init() {
	}

	/**
	 * @brief 본문출력 전 메시지 출력하기
	 **/
	function triggerDisplay_star_rating(&$output) {
/*		if((Context::get('module') != 'admin') && !Context::get('act'))
		{
			$message = '<font style="font-size:30px;font-weight:bold;">Triggersample 예제입니다.</font>';
			$message = 'xxxxx';
			//$stars = '<img class="zbxe_widget_output" widget="star_rating" document_srl="{$document->document_srl}"|cond="$document_srl!=$document->document_srl" />';

			//버그 있음
			//$output = str_replace('xxxxxx', $stars, $output);
			//$output = "{$message}{$output}";
		}*/

	}



	/**
	 * @brief 글 삭제시 별점 log 삭제
	 **/
	function triggerDelete_star_rating_log(&$obj) {

		$args->document_srl = $obj->document_srl;
	  
		if($args == NULL) return new Object(-1, "msg_error_occured");
	  
		// begin transaction
		$oDB = &DB::getInstance();
		$oDB->begin();
	  
	  	// 사용자 개별 평점 삭제
		$output = executeQuery('star_rating_config.deleteStar_rating_log', $args);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return new Object(-1, "msg_error_occured");
		}
	  
		// 글 평균삭제
		$output = executeQuery('star_rating_config.deleteStar_rating', $args);
		if(!$output->toBool())
		{
			$oDB->rollback();
			return new Object(-1, "msg_error_occured");
		}

		// commit
		$oDB->commit();
	}


	/**
	 * @brief 별점 추가하기
	 **/
	function star_rating_config_addstar(){

		if(Context::get('module_srl')) $args->module_srl = Context::get('module_srl');

		// 자바스크립트 함수인 exec_xml() 로 건네받은 값들
		$args->document_srl = Context::get('documentsrl'); // 해당 글
		$args->member_srl = Context::get('membersrl'); //해당 사람

		$rateval = Context::get('rating'); //점수
		$star_max = Context::get('starmax'); // 표시될 별의 개수
		$full_point = Context::get('fullpoint'); // 평균점수 표시 방법, 10점 만점 또는 별 표시 개수가 만점, 변수값 : as_ten, as_star_max
		$update_order = Context::get('updateorder');

		if (!$rateval or !$args->member_srl or !$args->document_srl) return;

		$ipadd = $_SERVER['REMOTE_ADDR']; //유저의 ip 주소
		$dat = date('YmdHis'); // 날짜
		
		// 점수를 저장하고 별개수 코드를 리턴한다.
		// db 테이블 star_rating_log->rateval 에는 개인 점수 저장
		// db 테이블 star_rating 에는 평균점수 저장
		
		//해당글 투표한 사람 수 구하기
		$args->rateval = 1; // 1점이상인 사람
		$output = executeQueryArray('widgets.star_rating.getStarRatedList', $args);
		$total = count($output->data);
		
		//해당글에 대한 투표 않했을 경우
		$output = executeQuery('widgets.star_rating.getStarRatedIs', $args);

		if(empty($output->data)) {
			$args->rateval = (int)$rateval;

			// 개인점수 저장
			$output = executeQueryArray('widgets.star_rating.insertStarRatingLog', $args);
			// 평균점수 구하기
			$staraverage = $this->get_star_average($args->document_srl);
			
			// 평균점수 저장
			$this->update_star_average($args->document_srl, $staraverage);

			// Updat_Order 최신글로 갱신
			if($update_order == 'Y') $this->update_document_order($args);

			// '별 개수'에 맞게 환산된 평점
			$converted_average = $staraverage * $star_max /10;
		
			// '부가정보'에 표시할 4/9와 같이 분모가 10 이하인 경우에 환산된 평균 점수 리턴
			if($full_point != '10') { 
				$staraverage = $denominator_average;
			}

			$this->add('persons', $total+1);
			$this->add('html_code', $html_code);
			$this->add('average', $staraverage);
			$this->add('converted_average', $converted_average);

			$this->add('member_srl', $args->member_srl);
			$this->add('document_srl', $args->document_srl);
			$this->add('star_max', $star_max);
			$this->add('update_order', $update_order);
			$this->add('val', $rateval);

		// 이미 투표한 경우
		} else {
			$this->add('member_srl', $args->member_srl);
			$this->add('document_srl', $args->document_srl);
			$this->add('star_max', $star_max);
			$this->add('update_order', $update_order);
			$this->add('val', $rateval);
			//echo json_encode($output);
			$this->message = 'already'; // message 는 success 가 기본값
		}
	}

	/**
	 * @brief 별점 제거하기
	 **/
	function star_rating_config_removestar() {
	
		$args->member_srl = Context::get('membersrl'); //해당 사람
		$args->document_srl = Context::get('documentsrl'); // 해당 글
		$star_max = Context::get('starmax'); // 표시될 별의 개수
		$full_point = Context::get('fullpoint'); // 평균점수 표시 방법, 10점 만점 또는 별 표시 개수가 만점, 변수값 : as_ten, as_star_max
		$update_order = Context::get('updateorder');
		
		if (!$args->member_srl or !$args->document_srl) return;
		
		$args->rateval = 1;
		$output = executeQueryArray('widgets.star_rating.getStarRatedList', $args);
		$total = count($output->data);
		
		$output = executeQueryArray('widgets.star_rating.getStarRatedIs', $args);

		if(!empty($output->data)) {
			$output = executeQueryArray('widgets.star_rating.deleteStarRatingLog', $args);
			$total-=1;

			// 평균점수 구하기
			$staraverage = $this->get_star_average($args->document_srl);
			
			// 평균점수 저장
			$this->update_star_average($args->document_srl, $staraverage);

			// Updat_Order 최신글로 갱신
			if($update_order == 'Y') $this->update_document_order($args);

			// '별 개수'에 맞게 환산된 평점
			$converted_average = $staraverage * $star_max /10;

			// '부가정보'에 표시할 4/9와 같이 분모가 10 이하인 경우에 환산된 평균 점수 리턴
			if($full_point != '10') { 
				$staraverage = $denominator_average;
			}

			//결과값 배열로 출력; 성공여부, 투표자총수, 별코드, 평균값
			$this->add('persons', $total);
			$this->add('html_code', $html_code);
			$this->add('average', $staraverage);
			$this->add('converted_average', $converted_average);

			$this->add('member_srl', $args->member_srl);
			$this->add('document_srl', $args->document_srl);
			$this->add('star_max', $star_max);
			$this->add('update_order', $update_order);

		} else {
			// 평균점수 구하기
			$staraverage = $this->get_star_average($args->document_srl);
			
			// 평균점수 저장
			$this->update_star_average($args->document_srl, $staraverage);

			// Updat_Order 최신글로 갱신
			//if($update_order == 'Y') $this->update_document_order($args);

			$this->add('member_srl', $args->member_srl);
			$this->add('document_srl', $args->document_srl);
			$this->add('star_max', $star_max);
			$this->add('update_order', $update_order);

			$this->message = 'already';
		}
	}

	// 평균점수 구하기
	function get_star_average($document_srl) {

		$db_info = Context::getDBInfo();
		$oDB=&DB::getInstance(); //xe의 DB 호출

		$table_log = $db_info->master_db['db_table_prefix'].'star_rating_log'; //개별

		// 투표 점수 평균 구하기
		//평균을 바로 구함
		$rateval_average = $oDB->_query("select avg(rateval) as average from $table_log where document_srl='$document_srl' group by document_srl");
		$output = $oDB->_fetch($rateval_average);
  
		$staraverage = empty($output->average) ? 0 : number_format($output->average,2); // sprintf("%01.2f", $staraverage)

		return $staraverage;
	}

	// 평균 점수 업데이트
	function update_star_average($document_srl, $staraverage) {
		// 평균점수는 정수로 저장되던 버그를 해결하기 위해
		// mysql에서 rate_average 필드값 형식을 float으로 변경했습니다.
		// insert와 update를 동시에 해줍니다.
		$args = new stdClass();

		$args->document_srl = $document_srl;
		$args->rateval = sprintf("%01.2f", $staraverage); //$staraverage;

		$output = executeQuery('widgets.star_rating.updateStarRatingAverage', $args);
		$output = executeQuery('widgets.star_rating.insertStarRatingAverage', $args);
	}

	function update_document_order($args) {
		// Updat_Order 최신글로 갱신
		$args->last_update = '';
		$args->update_order = getNextSequence()*-1;//($args->document_srl-(2*$args->document_srl)-1)*-1;//
		$output = executeQuery('widgets.star_rating.updateDocumentOrder', $args);

		return $output;
	}

}

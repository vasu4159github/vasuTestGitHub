<?php
	session_start();
	include "functions.php";
	//print_r($_POST);exit;
	/*if($_POST['tab_name']=='next'){
		list($quiz_nid,$quiz_vid,$child_nid,$child_vid,$weight) = explode("-",$_POST['question_str']);
	}else if($_POST['tab_name']=='skip')) {
		//list($quiz_nid,$quiz_vid,$child_nid,$child_vid,$weight) = explode("-",$_POST['question_str']);
		list($quiz_nid,$quiz_vid,$child_nid,$child_vid,$weight) = explode("-",$_POST['next_question_str']);
	}*/
	//if(!empty($_POST['question_str'])){$url_str=$_POST['question_str'];}else{$url_str=$_POST['next_question_str'];}
	
	// current tab values
	
	list($quiz_nid,$quiz_vid,$child_nid,$child_vid,$quiz_weight) = explode("-",$_POST['question_str']);
	
	
	
	//$quiz_nid=$_POST['quiz_nid'];
	//$quiz_vid=$_POST['quiz_vid'];
	$first_quiz_no=2;
	//$last_quiz_no=26;
	$last_quiz_no = $_POST['total_questions'];
	
	/********************* insert question result ***************************/
	
	// skip tab values
	if(($_POST['answer_id']=='undefined' && $_POST['tab_name']=='skip') || $_POST['tab_name']=='back'){
		list($curr_quiz_nid,$curr_quiz_vid,$curr_child_nid,$curr_child_vid,$curr_quiz_weight) = explode("-",$_POST['question_str']);
	}else if($_POST['tab_name']=='next'){
		list($curr_quiz_nid,$curr_quiz_vid,$curr_child_nid,$curr_child_vid,$curr_quiz_weight) = explode("-",$_POST['current_qs']);
	}
	
	$correct_answer = getAnswerForQuestion($curr_child_nid,$curr_child_vid);
	//print_r($correct_answer);
	//echo "******".$_POST['answer_id']."==".$correct_answer['id']."==".$_POST['tab_name'];
	//echo "session====".$_SESSION['result_id'];
	//$is_correct=0;$is_skipped=0;
	if($correct_answer['id']==$_POST['answer_id'] && $_POST['tab_name']=='next') {
		$is_correct=1;$is_skipped=0;
		$result = insertQuestionIdInResultAnswer($_SESSION['result_id'],$curr_child_nid,$curr_child_vid,$is_correct,$is_skipped);
	}else if($correct_answer['id']!=$_POST['answer_id'] && $_POST['tab_name']=='next') {
		//if ans wrong do
		$is_correct=0;$is_skipped=0;
		$result = insertQuestionIdInResultAnswer($_SESSION['result_id'],$curr_child_nid,$curr_child_vid,$is_correct,$is_skipped);
	}else if($_POST['answer_id']=='undefined' && $_POST['tab_name']=='skip') {
		$is_correct=0;$is_skipped=1;
		$result = insertQuestionIdInResultAnswer($_SESSION['result_id'],$curr_child_nid,$curr_child_vid,$is_correct,$is_skipped);
	}else if($_POST['tab_name']=='back') {
		// get
	}else{
		//echo "error in inserting.";
	}
	// if skip and last qs redirect to next page
	
	
	/******************* get next question **************************/
	if(($_POST['tab_name']=='next' && ($quiz_weight!=$last_quiz_no)) || ($_POST['tab_name']=='back' && ($quiz_weight!=$first_quiz_no))) {
		$weight=((int)$quiz_weight-1);
	}else{
		$weight = $quiz_weight;
	}
	$order_by_weight_asc = ' order by weight asc'; 
	$limit=" limit 3";
	// get quia_type and curr_question_no
	
	$quiz_qs_ids_result_set = getNextQuestionIdByQuizId($quiz_nid,$quiz_vid,$weight,$order_by_weight_asc,$limit);
	$quiz_questions_count = mysql_num_rows($quiz_qs_ids_result_set);
	if($_POST['tab_name']=='skip' && $quiz_weight==$last_quiz_no){
		$quiz_qs_ids_result_set=0;
	}
	if(mysql_num_rows($quiz_qs_ids_result_set)>=1){
		$all_question_ids_titles = array();
		$cc=0;
		while($quiz_qs_ids = mysql_fetch_assoc($quiz_qs_ids_result_set)){
			++$cc;
			
			$quiz_qs_ids=array_merge(array('curr_question_no' => $cc), $quiz_qs_ids);
			$all_question_ids_titles[$cc] = $quiz_qs_ids;
			
			//if($cc==2){break;}
		}		
	}else{
		echo "No question foud with the Quiz ".$quiz_nid;
	}
	//print_r($all_question_ids_titles);exit;
	$question_lable = array("a","b","c","d","e","f","g","h","i","j");
	//$quiz_question_number = $all_question_ids_titles[2]['curr_question_no'];
	if(!empty($all_question_ids_titles[1]['parent_nid'])){
		$back_question_id = $all_question_ids_titles[1]['parent_nid']."-".$all_question_ids_titles[1]['parent_vid']."-".$all_question_ids_titles[1]['child_nid']."-".$all_question_ids_titles[1]['child_vid']."-".$all_question_ids_titles[1]['weight'];
	}else{$back_question_id = '';}
	if(!empty($all_question_ids_titles[2]['parent_nid'])){
		$current_question_id = $all_question_ids_titles[2]['parent_nid']."-".$all_question_ids_titles[2]['parent_vid']."-".$all_question_ids_titles[2]['child_nid']."-".$all_question_ids_titles[2]['child_vid']."-".$all_question_ids_titles[2]['weight'];
	}else{$current_question_id = '';}
	if(!empty($all_question_ids_titles[3]['parent_nid'])){
		$next_question_id = $all_question_ids_titles[3]['parent_nid']."-".$all_question_ids_titles[3]['parent_vid']."-".$all_question_ids_titles[3]['child_nid']."-".$all_question_ids_titles[3]['child_vid']."-".$all_question_ids_titles[3]['weight'];
	}else{$next_question_id = '';}
	$out_put['text'] = '';
	$out_put['question'] = '';
	if($_POST['tab_name']=='back' && $quiz_weight==$first_quiz_no) {
		$out_put['text'] .= '<div class="quiz-question-body"><p><span id="qs_sno"></span><span>).&nbsp;&nbsp;</span>'.$all_question_ids_titles[1]['title'].'</p>';
	}else if(($_POST['tab_name']=='next' || $_POST['tab_name']=='skip') && $quiz_weight==$last_quiz_no){
		$out_put['text'] .= '<div class="quiz-question-body"><p><span id="qs_sno"></span><span>).&nbsp;&nbsp;</span>'.$all_question_ids_titles[1]['title'].'</p>';
	}else{
		$out_put['text'] .= '<div class="quiz-question-body"><p><span id="qs_sno"></span><span>).&nbsp;&nbsp;</span>'.$all_question_ids_titles[2]['title'].'</p>';
	}
	$out_put['text'] .= '</div><div class="form-item form-type-radios form-item-tries-answer"><label for="edit-tries-answer">Choose one </label>';
	$out_put['text'] .= '<div id="edit-tries-answer" class="form-radios"><table><tbody>';
	if($_POST['tab_name']=='back' && $quiz_weight==$first_quiz_no) {
		$answer_result_set = getAnswersByQuestionId($all_question_ids_titles[1]['child_nid'],$all_question_ids_titles[1]['child_vid']);
	}else if(($_POST['tab_name']=='next' || $_POST['tab_name']=='skip') && $quiz_weight==$last_quiz_no){
		$answer_result_set = getAnswersByQuestionId($all_question_ids_titles[1]['child_nid'],$all_question_ids_titles[1]['child_vid']);
	}else{
		$answer_result_set = getAnswersByQuestionId($all_question_ids_titles[2]['child_nid'],$all_question_ids_titles[2]['child_vid']);
	}
	if(mysql_num_rows($answer_result_set)>=1){
		$cc=0;
		while($answer = mysql_fetch_assoc($answer_result_set)){
			++$cc;
			$even_odd='';
			if($cc%2==0){$even_odd='even';}else{$even_odd='odd';}
		
		$out_put['text'] .= '<tr class="multichoice_row '.$even_odd.' jquery-once-1-processed"><td class="selector-td" width="15">'.$question_lable[($cc-1)].'</td><td class="selector-td" width="35"><div class="form-item form-type-radio form-item-tries-answer">';
		$out_put['text'] .= '<input id="edit-tries-answer-'.$answer['id'].'" name="tries" value="'.$answer['id'].'" class="form-radio" type="radio"></div></td><td><p>'.$answer['answer'].'</p></td> </tr>';
		}
	}
$out_put['text'] .= '</tbody></table><style>.tabs .tabs.primary{display:none;}</style></div></div>';
if($_POST['tab_name']=='back' && $quiz_weight==$first_quiz_no) {
	$out_put['question'] = array('ajax_back_qs_id'=>'','ajax_skip_qs_id'=>$back_question_id,'ajax_next_qs_id'=>$current_question_id);
}else if($_POST['tab_name']=='next' && $quiz_weight==$last_quiz_no){
	$last_quiz_str = $quiz_nid."-".$quiz_vid."-".$child_nid."-".$child_vid."-".($quiz_weight-1)."";
	$out_put['question'] = array('ajax_back_qs_id'=>$last_quiz_str,'ajax_skip_qs_id'=>$back_question_id,'ajax_next_qs_id'=>$back_question_id);
}else if($_POST['tab_name']=='skip' && $quiz_weight==$last_quiz_no){
	$last_quiz_str = $quiz_nid."-".$quiz_vid."-".$child_nid."-".$child_vid."-".($quiz_weight-1)."";
	$out_put['question'] = array('ajax_back_qs_id'=>$last_quiz_str,'ajax_skip_qs_id'=>'','ajax_next_qs_id'=>'');
}else{
	$out_put['question'] = array('ajax_back_qs_id'=>$back_question_id,'ajax_skip_qs_id'=>$current_question_id,'ajax_next_qs_id'=>$next_question_id);
}
//echo "<pre>";print_r($out_put);
echo json_encode($out_put);
?>
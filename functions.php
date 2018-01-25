<?php
	function getConnection() {
		mysql_connect("localhost","root","");
		mysql_select_db("quiz-balu");
		//return "connected";
	}
	getConnection();
	/*
	function getNodeById($quiz_id=0) {
		//getConnection();
		return mysql_query("select nid,vid from node where type='quiz' and nid=".$quiz_id);		
	}
	function getQuestionIdByNodeId($quiz_id=0,$quiz_vid=0) {
		//getConnection();
		return mysql_query("select parent_nid,parent_vid,child_nid,child_vid,weight from quiz_node_relationship where parent_nid=".$quiz_id." and parent_vid=".$quiz_vid);		
	}
	*/
	function insertQuizIdInResult($nid,$vid,$time_start,$uid) {
		$result = mysql_query("insert into quiz_node_results(nid,vid,uid,time_start,time_end,released,score,is_invalid,is_evaluated,time_left) value(".$nid.",".$vid.",".$uid.",".$time_start.",'0','0','0','0','0','0')");
		return mysql_insert_id();
	}
	
	function getAnswerForQuestion($child_nid,$child_vid){
		$result = mysql_query("SELECT * FROM quiz_multichoice_answers WHERE question_nid=".$child_nid." and question_vid=".$child_vid." and score_if_chosen=1");
		$result = mysql_fetch_assoc($result);
		return $result;
	}
	function getUserGivenAnswerInUserAnswers($result_id,$question_nid,$question_vid) {
		//$result = mysql_query("select id,result_id from quiz_multichoice_user_answers where question_nid=".$question_nid." and question_vid=".$question_vid." and result_id=".$result_id);
		
		$result = mysql_query("select qmua.id,qmua.result_id from quiz_node_results_answers qnra join quiz_multichoice_user_answers qmua on qnra.question_nid=qmua.question_nid and qnra.question_vid=qmua.question_vid and qnra.result_id=qmua.result_id where qnra.question_nid=".$question_nid." and qnra.question_vid=".$question_vid." and qnra.result_id=".$result_id." and qnra.is_skipped=1");
		$result = mysql_fetch_assoc($result);
		return $result;
	}
	
	function insertQuestionIdInResultAnswer($result_id,$qs_nid,$qs_vid,$correct,$skipped,$weight) {
		mysql_query("insert into quiz_node_results_answers(result_id,question_nid,question_vid,tid,is_correct,is_skipped,points_awarded,answer_timestamp,number,is_doubtful) value(".$result_id.",".$qs_nid.",".$qs_vid.",'0',".$correct.",".$skipped.",".$correct.",'0',".$weight.",'0')");
	}
	
	function insertResultInUserAnswers($result_id,$question_nid,$question_vid){
		mysql_query("insert into quiz_multichoice_user_answers(result_id,question_nid,question_vid,choice_order) value(".$result_id.",".$question_nid.",".$question_vid.",'')");
		return $user_answer_id = mysql_insert_id();
	}
	function insertResultInUserAnswersMulti($user_answer_id,$answer_id){
		mysql_query("insert into quiz_multichoice_user_answer_multi(user_answer_id,answer_id) value(".$user_answer_id.",".$answer_id.")");
	}
	function getUserGivenAnswer($result_id,$question_nid,$question_vid) {
		$result = mysql_query("select uam.* from quiz_multichoice_user_answers ua join quiz_multichoice_user_answer_multi uam on ua.id=uam.user_answer_id where ua.question_nid=".$question_nid." and ua.question_vid=".$question_vid." and ua.result_id=".$result_id);
		$result = mysql_fetch_assoc($result);
		return $result;
	}
	function updateUserAnswer($user_answer_id,$answer_id){
		mysql_query("update quiz_multichoice_user_answer_multi set answer_id=".$answer_id." where user_answer_id=".$user_answer_id);
		return "updated";
	}
	function updateUserAnswerStatus($result_id,$question_nid,$question_vid,$correct,$skipped){
		mysql_query("update quiz_node_results_answers set is_correct=".$correct.",is_skipped=".$skipped.",points_awarded=".$correct." where result_id=".$result_id." and question_nid=".$question_nid." and question_vid=".$question_vid);
		return "updated";
	}
	
	function updateQuizEvaluated($result_id,$quiz_nid,$quiz_vid,$evaluated,$end_time){
		mysql_query("update quiz_node_results set is_evaluated=".$evaluated.",time_end=".$end_time." where result_id=".$result_id." and nid=".$quiz_nid." and vid=".$quiz_vid);
		return "updated";
	}
	
	
	
	
	
	
	
	
	/*********** 12-11-2013 ****************/
	function getUserQuizStatus($uid) {
		$result=array('result_id'=>"",'is_evaluated'=>"");
		$result = mysql_query("select result_id,is_evaluated from quiz_node_results where uid=".$uid." order by result_id desc limit 0,1");
		if(mysql_num_rows($result)>0){
			$result = mysql_fetch_assoc($result);			
		}
		return $result;
	}
	function getUserPastQuestion($uid) {
		//getConnection();
		$past_qs='';
		$result = mysql_query("select qnr.result_id,qnr.nid,qnr.vid,qnr.time_left,qnr.time_end,qnr.time_start,qmua.question_nid,qmua.question_vid,qnra.number from quiz_node_results qnr left join quiz_multichoice_user_answers qmua on qnr.result_id=qmua.result_id join quiz_node_results_answers qnra on qmua.question_nid=qnra.question_nid and qmua.question_vid=qnra.question_vid where qnr.uid=".$uid." and qnr.is_evaluated=0 order by qmua.id desc limit 0,1");
		
		if(mysql_num_rows($result)>0){
			$result = mysql_fetch_assoc($result);
			$past_qs = $result['nid']."-".$result['vid']."-".$result['question_nid']."-".$result['question_vid']."-".$result['number']."_".$result['result_id'];
		}
		return $past_qs;
	}
	/*********** 12-11-2013 ****************/

	
	
	function getQuestionIdByQuizId($quiz_id=0,$weight=0,$limit='') {
		//getConnection();
		return $result = mysql_query("SELECT n1.title,n.nid,n.vid,nre.parent_nid,nre.parent_vid,nre.child_nid,nre.child_vid,nre.weight FROM node n 
		join quiz_node_relationship nre on n.nid=nre.parent_nid and n.vid=nre.parent_vid 
		join node n1 on n1.nid=nre.child_nid and n1.type='multichoice' WHERE n.type='quiz' and n.nid=".$quiz_id." order by nre.weight asc".$limit);
	}
	
	function getAnswersByQuestionId($question_nid,$question_vid) {
		//getConnection();
		return $result = mysql_query("select id,answer,score_if_chosen,score_if_not_chosen from quiz_multichoice_answers where question_nid=".$question_nid." and question_vid=".$question_vid);
	}
	
	function getNextQuestionIdByQuizId($quiz_nid=0,$quiz_vid=0,$weight=0,$orderby='',$limit='') {
		//getConnection();
		return $result = mysql_query("SELECT n.title,qr.parent_nid,qr.parent_vid,qr.child_nid,qr.child_vid,qr.weight FROM quiz_node_relationship qr join node n on qr.child_nid=n.nid and n.type='multichoice' where qr.parent_nid=".$quiz_nid." and qr.parent_vid=".$quiz_vid." and qr.weight>=".$weight.$orderby.$limit);
	}
	function deleteUserAnswer($user_answer_id){
		mysql_query("delete from quiz_multichoice_user_answer_multi where user_answer_id=".$user_answer_id);
		return "deleted";
	}
	/*function getQuestionIdByQuizId2($quiz_id=0,$weight=0) {
		//getConnection();
		return $result = mysql_query("SELECT n1.title,n.nid,n.vid,nre.parent_nid,nre.parent_vid,nre.child_nid,nre.child_vid,nre.weight FROM node n 
		join quiz_node_relationship nre on n.nid=nre.parent_nid and n.vid=nre.parent_vid and nre.weight=".$weight." 
		join node n1 on n1.nid=nre.child_nid and n1.type='multichoice' WHERE n.type='quiz' and n.nid=".$quiz_id);
	}
	*/
	
	/*
	function getAll($quiz_id) {
		//getConnection();
		return $result = mysql_query("SELECT  n1.title,n.nid,n.vid,nre.parent_nid,nre.parent_vid,nre.child_nid,nre.child_vid,nre.weight FROM node n 
		join quiz_node_relationship nre on n.nid=nre.parent_nid and n.vid=nre.parent_vid 
		join node n1 on n1.nid=nre.child_nid and n1.type='multichoice' WHERE n.type='quiz' and n.nid=".$quiz_id." order by nre.weight asc");
	}
	*/
	/*************************git test here******************************/
	function feature1(){
		//some 
	}
	
?>	
<?php 
ob_start();
function quiz_manage() {

    $tab  = ( ! empty( $_GET ) && ! empty( $_GET['action'] ) && 'edit' === $_GET['action'] ) ? 'edit' : 'new'; // phpcs:ignore.

    $post_type = (isset($_POST['post_type'])) ? true : false;

    $edit_post = (isset($_POST['edit_post'])) ? true : false;

    if(!isset($_GET['type'])){
        redirect_page(admin_url('admin.php?page=quiz_manage&action=new&type=quiz'));
    }

    if($post_type) {
        handle_data_submit($_POST[$_GET['type']], $_GET['type']);
    }else if($_GET['action'] == 'delete') {
        if(!isset($_GET['id'])){
            redirect_page(admin_url('admin.php?page='.pageRedirect($_GET['type'])."'"));
        }

        $result = delete_data_by_id($_GET['type'], $_GET['id']);

    }else if($edit_post) {
        edit_data($_POST[$_GET['type']], $_GET['type']);
    }
    else { 
        if($_GET['type'] == 'quiz'){
            if(!isset($_GET['id']) && $tab == 'edit'){
                redirect_page(admin_url('admin.php?page=quiz_listing'));
            }
            $data = [];
            $id = (isset($_GET['id'])) ? $_GET['id'] : "";
            if($tab == 'edit'){
                $data = get_data_by_id($_GET['type'],$id);
            }
            render_quiz_form($tab, $id,$data);

        }elseif($_GET['type'] == 'type') {  
            if(!isset($_GET['id']) && $tab == 'edit'){
                redirect_page(admin_url('admin.php?page=quiz_type'));
            }
            $data = [];
            $id = (isset($_GET['id'])) ? $_GET['id'] : "";
            if($tab == 'edit'){
                $data = get_data_by_id($_GET['type'],$id);
            }
            render_type_form($tab, $id,$data);
        }elseif($_GET['type'] == 'answer'){
            if(!isset($_GET['id']) && $tab == 'edit'){
                redirect_page(admin_url('admin.php?page=quiz_answer'));
            }
            $data = [];
            $id = (isset($_GET['id'])) ? $_GET['id'] : "";
            if($tab == 'edit'){
                $data = get_data_by_id($_GET['type'],$id);
            }
            render_answer_form($tab, $id,$data);
        }
    }

        
}

function render_quiz_form($tab, $id = '',$data = []){
    
    $typeList = get_data('type');
    $typeAnswer = get_data('answer');
    if($tab == "edit") {
        if($id == '') {
            redirect_page(admin_url('admin.php?page=quiz_listing'));
        }
        ?>
        <div class="wrap">
            <div class="atn-container">
                <div class="atn-title">
                    <p>Edit Quiz Information</p>
                </div>
                <form action="<?= admin_url('admin.php?page=quiz_manage&action=edit&type=quiz')?>" class="edit-form" method="POST">
                    <input type="hidden" name="quiz[id]" value="<?=$data->id?>">
                    <div class="item">
                        <label for="type">Type</label>
                        <select name="quiz[type_id]">
                            <?php foreach($typeList as $typeItem) : ?>
                                <option <?= $typeItem['id'] == $data->type_id ? "selected" : "" ?> value="<?= $typeItem['id']?>"><?=$typeItem['name']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="item">
                        <label for="answer">Answer</label>
                        <select name="quiz[answer_id]" id="answer">
                        <?php foreach($typeAnswer as $answerItem) : ?>
                                <option <?= $answerItem['id'] == $data->answer_id ? "selected" : "" ?> value="<?= $answerItem['id']?>"><?=$answerItem['name']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="item">
                        <label for="quizText">Content</label>
                        <input id="content" name="quiz[quiz_text]" type="text" value="<?= $data->quiz_text ?>"></textarea>
                    </div>
                    <div class="item">
                        <label for="quizText">Position</label>
                        <input id="content" name="quiz[position]" type="text" value="<?= $data->position ?>"></textarea>
                    </div>
                    <input type="hidden" name="edit_post" value="true"/>
                    <div class="action">
                        <button type="submit button-primary" id="btnEdit">Edit Quiz</button>
                    </div>
                </form>
            </div>
        </div>
    <?php
    } else {
    ?>  
        <div class="wrap">
            <div class="atn-container">
                <ul>
                    <li class="active"><a href="<?= admin_url('admin.php?page=quiz_manage&action=new&type=quiz')?>">Add Quiz</a></li>
                    <li><a href="<?= admin_url('admin.php?page=quiz_manage&action=new&type=type')?>">Add Quiz Type</a></li>
                    <li><a href="<?= admin_url('admin.php?page=quiz_manage&action=new&type=answer')?>">Add Quiz Answer</a></li>
                </ul>
                <div class="atn-title">
                    <p>Quiz Information</p>
                </div>
                <div class="more-action">
                    <button type="button" class="button-primary btnMore" data-type="quiz">+</button>
                </div>
                <form action="<?= admin_url('admin.php?page=quiz_manage&action=new&type=quiz')?>" method="POST">
                    <div class="quiz-title">
                        <div class="quiz-item">
                            <div class="item">
                                <p for="type">Type</p>
                            </div>
                            <div class="item">
                                <p for="answer">Answer</p>
                            </div>
                            <div class="item">
                                <p for="quizText">Content</p>
                            </div>
                            <div class="item">
                                <p for="quizText">Position</p>
                            </div>
                        </div>
                    </div>
                    <div class="quiz">
                        <div class="quiz-item">
                            <div class="item">
                                <select name="quiz[0][type_id]" id="optionType">
                                    <?php foreach($typeList as $typeItem) : ?>
                                        <option value="<?= $typeItem['id']?>"><?=$typeItem['name']?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="item">
                                <select name="quiz[0][answer_id]" id="optionAnswer">
                                <?php foreach($typeAnswer as $answerItem) : ?>
                                        <option value="<?= $answerItem['id']?>"><?=$answerItem['name']?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="item">
                                <input  name="quiz[0][quiz_text]" type="text" value="">
                            </div>
                            <div class="item">
                                <input  name="quiz[0][position]" type="text" value="0">
                            </div>
                        </div>
                        <div class="answer" style="display:none">
                        <div class="answer-list">
                            <p class="close-popup">x</p>
                            <div class="answer-item">
                                <label>Answer 1</label>
                                <input type="text" name="answer[item][]" class="answer-input" value="">
                            </div>
                            <div class="answer-item">
                                <label>Answer 2</label>
                                <input type="text" name="answer[item][]" class="answer-input" value="">
                            </div>
                            <div class="answer-item">
                                <label>Answer 3</label>
                                <input type="text" name="answer[item][]" class="answer-input" value="">
                            </div>
                            <div class="answer-item">
                                <label>Answer 4</label>
                                <input type="text" name="answer[item][]" class="answer-input" value="">
                            </div>
                            <div class="action">
                                <button type="button" class="button-primary" disabled id="btnAddAnswer">Add Answer</button>
                            </div>
                        </div>
                    </div>
                    </div>
                    <input type="hidden" name="post_type" value="true"/>
                    <div class="action">
                        <button type="submit" id="btnCreate">Add Quiz</button>
                    </div>
                </form>
            </div>
        </div>
    <?php
    }
}

function render_answer_form($tab, $id = '',$data = []){
    if($tab == "edit") {
        if($id == '') {
            redirect_page(admin_url('admin.php?page=quiz_answer'));
        }
        ?>
        <div class="wrap">
            <div class="atn-container">
                <div class="atn-title">
                    <p>Edit Answer Information</p>
                </div>
                <form action="<?= admin_url('admin.php?page=quiz_manage&action=edit&type=answer')?>" class="answer-form edit-form" method="POST">
                    <input type="hidden" name="answer[id]" value="<?=$data->id?>">
                    <div class="item">
                        <label for="quizText">Name</label>
                        <input id="content" name="answer[name]" type="text" value="<?= $data->name ?>"></textarea>
                        <div class="">
                            <a href="" class="btnOpenPopup"><?= count(get_answer_option_by_answer_id($data->id)) > 0 ? "Edit Options" :  "Add Option" ?></a>
                        </div>
                    </div>
                    <div class="answer" style="display:none">
                        <div class="answer-list">
                            <p class="close-popup">x</p>
                            <?php 
                                $i = 0;
                                $options = get_answer_option_by_answer_id($data->id);
                                $index = 0;
                                if(count($options) > 0 && count($options) <= 4){
                                    $index = 4 - count($options);
                                }else {
                                    $index = 4;
                                }
                            ?>
                            <?php foreach($options as $key => $option): ?>
                                <?php $i++; ?>
                            <div class="answer-item">
                                <label>Answer <?=$i?></label>
                                <input type="hidden" name="answer[item][<?=$option->id?>][id]" class="answer-id" value="<?= $option->id ?>">
                                <input type="hidden" name="answer[item][<?=$option->id?>][delete]" class="answer-delete" value="0">
                                <input type="text" name="answer[item][<?=$option->id?>][content]" style="margin-bottom: 10px" class="answer-input" value="<?= $option->content ?>">
                                <input type="text" name="answer[item][<?=$option->id?>][score]" class="answer-score" value="<?= $option->score ?>">
                            </div>
                            <?php endforeach; ?>
                            <?php  if($index > 0):?>
                                <?php for($y = 1; $y <= $index; $y++): ?>
                                    <?php $i++; ?>
                                    <div class="answer-item">
                                        <label>Answer <?=$i?></label>
                                        <input type="text" style="margin-bottom: 10px" name="answer[item][<?=$i?>][content]" class="answer-input" value="">
                                        <input type="text" name="answer[item][<?=$i?>][score]" class="answer-score" value="0">
                                    </div>
                                <?php endfor;?>
                            <?php endif;?>
                            <div class="action">
                                <button type="button" class="button-primary" disabled id="btnAddAnswer"><?= count(get_answer_option_by_answer_id($data->id)) > 0 ? "Edit Options" :  "Add Option" ?></button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="edit_post" value="true"/>
                    <input type="hidden" name="answer[edit-option]" value="false"/>
                    <div class="action">
                        <button type="submit button-primary" id="btnEdit">Edit Answer</button>
                    </div>
                </form>
            </div>
        </div>
    <?php
    } else {
    ?>  
        <div class="wrap">
            <div class="atn-container">
                <ul>
                    <li><a href="<?= admin_url('admin.php?page=quiz_manage&action=new&type=quiz')?>">Add Quiz</a></li>
                    <li><a href="<?= admin_url('admin.php?page=quiz_manage&action=new&type=type')?>">Add Quiz Type</a></li>
                    <li class="active"><a href="<?= admin_url('admin.php?page=quiz_manage&action=new&type=answer')?>">Add Quiz Answer</a></li>
                </ul>
                <div class="atn-title">
                    <p>Answer Information</p>
                </div>
                <form action="<?= admin_url('admin.php?page=quiz_manage&action=new&type=answer')?>" class="answer-form " method="POST">
                    <div class="quiz-title">
                        <div class="quiz-item">
                            <div class="item">
                                <p for="type">Name</p>
                            </div>
                            <div class="item">
                                <p for="type">Option's</p>
                            </div>
                            <div class="item">
                                <p for="type">Action</p>
                            </div>
                        </div>
                    </div>
                    <div class="quiz">
                        <div class="quiz-item">
                            <div class="item">
                                <input  name="answer[name]" type="text" value="">
                            </div>
                            <div class="item">
                                <p class="number-options">No Options</p>
                            </div>
                            <div class="item">
                                <a href="" class="btnOpenPopup">Add Option</a>
                            </div>
                        </div>
                    </div>
                    <div class="answer" style="display:none">
                        <div class="answer-list">
                            <p class="close-popup">x</p>
                            <div class="answer-item">
                                <label>Option 1</label>
                                <input type="text" style="margin-bottom: 10px" placeholder="content" name="answer[item][0][]" class="answer-input" value="">
                                <input type="text" placeholder="score" name="answer[item][0][]" class="answer-score" value="0">
                            </div>
                            <div class="answer-item">
                                <label>Option 2</label>
                                <input type="text" style="margin-bottom: 10px" placeholder="content" name="answer[item][1][]" class="answer-input" value="">
                                <input type="text" placeholder="score" name="answer[item][1][]" class="answer-score" value="0">
                            </div>
                            <div class="answer-item">
                                <label>Option 3</label>
                                <input type="text" style="margin-bottom: 10px" placeholder="content" name="answer[item][2][]" class="answer-input" value="">
                                <input type="text" placeholder="score" name="answer[item][2][]" class="answer-score" value="0">
                            </div>
                            <div class="answer-item">
                                <label>Option 4</label>
                                <input type="text" style="margin-bottom: 10px" placeholder="content" name="answer[item][3][]" class="answer-input" value="">
                                <input type="text" placeholder="score" name="answer[item][3][]" class="answer-score" value="0">
                            </div>
                            <div class="action">
                                <button type="button" class="button-primary" disabled id="btnAddAnswer">Add Answer</button>
                            </div>
                        </div>
                    </div>
                    <div class="action">
                        <button type="submit" id="btnCreate">Add Answer</button>
                    </div>
                    <input type="hidden" name="post_type" value="true"/>
                    <input type="hidden" name="add_answer" value="false"/>
                </form>
            </div>
        </div>
    <?php
    }
}

function render_type_form($tab, $id = '',$data = []){
    $typeList = get_data('type');
    if($tab == "edit") {
        if($id == '') {
            redirect_page(admin_url('admin.php?page=quiz_type'));
        }
        ?>
        <div class="wrap">
            <div class="atn-container">
                <div class="atn-title">
                    <p>Edit Type Information</p>
                </div>
                <form action="<?= admin_url('admin.php?page=quiz_manage&action=edit&type=type')?>" class="edit-form" method="POST">
                    <input type="hidden" name="type[id]" value="<?=$data->id?>">
                    <div class="item">
                        <label for="quizText">Name</label>
                        <input id="content" name="type[name]" type="text" value="<?= $data->name ?>"></textarea>
                    </div>
                    <div class="item">
                        <label for="quizText">Code</label>
                        <input id="content" name="type[code]" type="text" value="<?= $data->code ?>"></textarea>
                    </div>
                    <input type="hidden" name="edit_post" value="true"/>
                    <div class="action">
                        <button type="submit button-primary" id="btnEdit">Edit Type</button>
                    </div>
                </form>
            </div>
        </div>
    <?php
    } else {
    ?>  
        <div class="wrap">
            <div class="atn-container">
                <ul>
                    <li><a href="<?= admin_url('admin.php?page=quiz_manage&action=new&type=quiz')?>">Add Quiz</a></li>
                    <li class="active"><a href="<?= admin_url('admin.php?page=quiz_manage&action=new&type=type')?>">Add Quiz Type</a></li>
                    <li><a href="<?= admin_url('admin.php?page=quiz_manage&action=new&type=answer')?>">Add Quiz Answer</a></li>
                </ul>
                <div class="atn-title">
                    <p>Type Information</p>
                </div>
                <div class="more-action">
                    <button type="button" class="button-primary btnMore" data-type="type">+</button>
                </div>
                <form action="<?= admin_url('admin.php?page=quiz_manage&action=new&type=type')?>" method="POST">
                    <div class="quiz-title">
                        <div class="quiz-item">
                            <div class="item">
                                <p for="type">Name</p>
                            </div>
                            <div class="item">
                                <p for="answer">Code</p>
                            </div>
                        </div>
                    </div>
                    <div class="quiz">
                        <div class="quiz-item">
                            <div class="item">
                                <input  name="type[0][name]" type="text" value="">
                            </div>
                            <div class="item">
                                <input  name="type[0][code]" type="text" value="">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="post_type" value="true"/>
                    <div class="action">
                        <button type="submit" id="btnCreate">Add Type</button>
                    </div>
                </form>
            </div>
        </div>
    <?php
    }
}

function get_data_by_id($type, $id) {
    global $wpdb;
    $table_name = $wpdb->prefix.get_table_name($type); 

    $data = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE id = $id",
    );
    return $data[0];
}

function get_answer_option_by_answer_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix."quiz_answer_collection_mental_health"; 

    $data = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE answer_id = $id",
    );
    return $data;
}

function get_data($type) {
    global $wpdb;
    $table_name = ($type == 'type') ? $wpdb->prefix."quiz_type_mental_health" : $wpdb->prefix."quiz_answer_mental_health";
    $data = $wpdb->get_results(
        "SELECT * from $table_name",
        ARRAY_A
);
    return $data;
}

function redirect_page($url) {
    wp_redirect($url);
    exit;
}

function handle_data_submit($data, $type) {
    global $wpdb;
    $i = 0;
    
    if ( current_user_can( 'administrator' ) ) {
        
        $table_name = $wpdb->prefix.get_table_name($type);
        foreach($data as $key => $item){

            if($type == 'answer') {
                $sql_checkAnswer = conditionQuery($type, $table_name, $item);
                
                if($wpdb->get_var($sql_checkAnswer) <= 0) {

                    if($key == 'name') {
                        $result = $wpdb->insert(
                            $table_name,
                            array($key => $item)
                        );

                        if($result) {
                            $i++;
                            $last_id = $wpdb->insert_id;
                            if($data['item']) {
                                foreach($data['item'] as $option)
                                {
                                    if(count($option) > 1) {
                                        $arr = array(
                                            "answer_id" => $last_id,
                                            "content" => $option[0],
                                            "score" => $option[1]
                                        );
    
                                        $wpdb->insert(
                                            "wp_quiz_answer_collection_mental_health",
                                            $arr
                                        );
                                    }
                                }
                                unset($data['item']);
                            }
                        }
                    }
                }
            }
            else {
                $sql_check = conditionQuery($type, $table_name, $item);
    
                if($wpdb->get_var($sql_check) <= 0) {
                    
                    $result = $wpdb->insert(
                        $table_name,
                        $item
                    );
        
                    if($result) {
                        $i++;
                    }
                }
            }
        }
    
    }
    nofifyAction($type, 'added', $i);

    redirect_page('admin.php?page='.pageRedirect($type)."'");
    
}

function delete_data_by_id($type, $id) {
    global $wpdb;
    $table_name = $wpdb->prefix.get_table_name($type);

    $i = 0;
    

    if(is_array($id))
    {
        foreach($id as $item) {
        $sql_check = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE  id = %s",$item);
            if($wpdb->get_var($sql_check) > 0) {

                $where = array( 'id' => $item );

                $result = $wpdb->delete(
                    $table_name,
                    $where
                );

                if($result) {
                    $i++;
                }
            }
        }
    }else  {
        $sql_check = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE  id = %s",$id);
            if($wpdb->get_var($sql_check) > 0) {

                if($type == 'answer') {
                    $option = array( 'answer_id' => $id );
                
                    $wpdb->delete(
                        "wp_quiz_answer_collection_mental_health",
                        $option
                    );  
                }

                $where = array( 'id' => $id );

                $result = $wpdb->delete(
                    $table_name,
                    $where
                );

                if($result) {
                    $i++;
                    
                }
            }
    }

    nofifyAction($type, 'deleted', $i);

    redirect_page('admin.php?page='.pageRedirect($type)."'");
}

function edit_data($data, $type){
    global $wpdb;
    $table_name = $wpdb->prefix.get_table_name($type);

    $i = 0;
    $sql_check = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE  id = %s", $data['id']);

    if($wpdb->get_var($sql_check) > 0) {

        if($type == 'answer') {
            if($data['edit-option'] == true){
                foreach($data['item'] as $item)
                {
                    if(isset($item['id'])) {
                        $optionid = array("id" => $item['id']);
                        if(!$item['delete']) {
                            $arr = array(
                                "answer_id" => $data['id'],
                                "content" => $item['content'],
                                "score" => $item['score']
                            );
        
                            $wpdb->update(
                                "wp_quiz_answer_collection_mental_health",
                                $arr,
                                $optionid
                            );
                        }else {
                            $wpdb->delete(
                                "wp_quiz_answer_collection_mental_health",
                                $optionid
                            );
                        }
                    }else {
                        if($item['content']){
                            $arr = array(
                                "answer_id" => $data['id'],
                                "content" => $item['content'],
                                "score" => $item['score']
                            );
        
                            $wpdb->insert(
                                "wp_quiz_answer_collection_mental_health",
                                $arr
                            );
                        }
                    }
                }
                unset($data['item']);
                unset($data['edit-option']);
            }else {
                unset($data['item']);
                unset($data['edit-option']);
            }
        }
        $where = array( 'id' => $data['id'] );

        $result = $wpdb->update(
            $table_name,
            $data,
            $where
        );

        if($result) {
            $i++;
        }
        nofifyAction($type, 'updated', $i);
    }else {
        $result = $wpdb->insert(
            $table_name,
            $data
        );

        if($result) {
            $i++;
        }

        nofifyAction($type, 'added', $i);
    }


    redirect_page('admin.php?page='.pageRedirect($type)."'");
    
}
function get_table_name($type){
    switch($type) {
        case "type":
            $table_name = "quiz_type_mental_health";
            break;
        case "answer":
            $table_name = "quiz_answer_mental_health";
            break;
        default: 
            $table_name = "quiz_mental_health";
    }

    return $table_name;
}

function nofifyAction($type,$action, $count) {
    $status = ($count > 0) ? "successfully" : "failed";

    $message['text'] = ucfirst($type) . " ( " . $count . " ) " . $action . " " .$status;
    $message['count'] = $count;


    set_transient('message', $message, 60*60*12);
} 

function conditionQuery($type, $table_name, $data){

    global $wpdb;

    $sql_check = false;

    if($type == 'quiz') {
        $sql_check = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE type_id = %s AND answer_id = %s AND quiz_text = %s", $data['type_id'], $data['answer_id'],$data['quiz_text']);
    }else if($type == 'type') {
        $sql_check = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE name = %s AND code = %s", $data['name'], $data['code']);
    }else if($type == 'anwser') {
        $sql_check = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE name = %s", $data['name'], $data['code']);
    }

    return $sql_check;
}

function pageRedirect($type){
    $page = '';
    if($type == 'quiz') {
        $page = 'quiz_listing';
    }else if($type == 'type')
    {
        $page = 'quiz_type';
    }else {
        $page = 'quiz_answer';
    }

    return $page;
}
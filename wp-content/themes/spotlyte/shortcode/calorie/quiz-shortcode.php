<?php

function quiz_style() {
    wp_enqueue_script('jquery');
	wp_enqueue_style( 'quiz-css', get_template_directory_uri() . '/shortcode/calorie/assets/css/quiz.css', '', '1.0.0');
	wp_enqueue_script( 'quiz-js', get_template_directory_uri() . '/shortcode/calorie/assets/js/quiz-plugin.js','','1.0.0');
}

add_action('init', 'quiz_style');

function get_quiz($code) {
    global $wpdb;

    $query = $wpdb->prepare(
        "SELECT q.quiz_text, q.answer_id, t.name
        FROM {$wpdb->prefix}quiz_mental_health AS q
        JOIN {$wpdb->prefix}quiz_type_mental_health AS t ON q.type_id = t.id
        WHERE t.code = %s
        ORDER BY q.position ASC",
        $code
    );
    
    $results = $wpdb->get_results($query);

    return $results;
}

function get_anwser_option($answer_id)
{
    global $wpdb;

    $query = $wpdb->prepare(
        "SELECT *
        FROM {$wpdb->prefix}quiz_answer_collection_mental_health 
        WHERE  answer_id = %s",
        $answer_id
    );
    
    $results = $wpdb->get_results($query);
    
    return $results;
}

function create_shortcode_tool_quiz($args, $content) {
	ob_start();

    $code = isset($args['code']) ? $args['code'] : '';
    $caption = isset($args['caption']) ? $args['caption'] : 'Quiz';
    $items = get_quiz($code);
    $result = 0;
	?>
    <div class="quiz-container">
        <div class="quiz-caption">
            <h2><?= $caption ?></h2>
        </div>
        <div class="quiz-instructions">
            <h3>Instructions</h3>
            <div class="description">
                <p class="">Simply answer the questions on how you have behaved and felt during the past 6 months. Take your time and answer truthfully for the most accurate results.</p>
            </div>
        </div>
        <div class="quiz-content">
            <div class="quiz-list">
                <?php foreach($items as $item): ?>
                    <?php $i = 0; 
                        $data = get_anwser_option($item->answer_id);
                    ?>
                    <div class="quiz-item">
                        <div class="quiz-title">
                            <h3><?=$item->quiz_text?></h3>
                        </div>
                        <div class="quiz-option">
                            <?php foreach($data as $option):?>
                                <div class="option"><p data-value="<?=$i?>"><?= $option->content ?></p></div>
                                <?php $i++; 
                                    if($i == count($data) - 1){
                                        $result += $i;
                                    }
                                ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
            <input type="hidden" name="total" value="<?=$result?>">
            <div class="result">
                <div class="top">
                    <div class="">
                        <p>Your result</p>
                    </div>
                    <div class="">
                        <p>Adult ADD Quiz</p>
                    </div>
                    <div class="">
                        <p style="font-size: 32px"><?=$items[0]->name?></p>
                    </div>
                </div>
                <div class="bottom">
                    <p style="font-size: 24px;font-weight: 600"></p>
                </div>
            </div>
            <div class="quiz-action">
                <button type="button" id="quizAction">GET RESULTS</button>
            </div>
        </div>
    </div>
	<?php 
	$rt = ob_get_clean();
	return $rt;
}
add_shortcode( 'hc_tool_quiz', 'create_shortcode_tool_quiz' );
/* call ajax tool */

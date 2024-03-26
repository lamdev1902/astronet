<?php

function quiz_style() {
	wp_enqueue_style( 'quiz-css', get_template_directory_uri() . '/shortcode/calorie/assets/css/quiz.css', '', '1.0.0');
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
	?>
    <div class="quiz-container">
        <div class="quiz-caption">
            <h2></h2>
        </div>
        <div class="quiz-instructions">
            <h3>Instructions</h3>
            <div class="description">
                <p class="">Simply answer the questions on how you have behaved and felt during the past 6 months. Take your time and answer truthfully for the most accurate results.</p>
            </div>
        </div>
        <div class="quiz-content">
            <div class="quiz-list">
            </div>
            <input type="hidden" name="total" value="">
            <div class="result">
                <div class="top">
                    <div class="">
                        <p>Your result</p>
                    </div>
                    <div class="">
                        <p>Adult ADD Quiz</p>
                    </div>
                    <div class="">
                        <p style="font-size: 32px"></p>
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

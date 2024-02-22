<?php 
$data = $atts['data'];

if($data)
{
    $limited_array = array_slice($atts['data'], 0, 9);

$count = 0;

foreach($data as $review)
{
    $count+= $review->review_count;
}
}
?>

<?php if($data):?>
    <div class="review-container">
    <ol class="review-items">
        <?php foreach($limited_array as $item): ?>
        <li class="review-item">
            <div class="review-item-header">
                <div class="review-header-rating">
                    <div class="review-ratings">
                    <?php 
                        $average = (int)$item->review_count;
                        $whole = floor($average);   
                        $fraction = $average - $whole; 
                        $averagefloat = abs($average); 
                        $percent = $fraction * 100;
                        $ratingClassFull = '';
                        $ratingClassEmpty = '';
                        $ratingClassPercent = '';
                        for ($i = 1; $i <= 5; $i++) { 
                            if ($i <= $averagefloat) {
                                $ratingClassFull .= "
                                <span class='star'>
                                    <svg xmlns='http://www.w3.org/2000/svg' height='18' width='18' viewBox='0 0 24 22' increment='0.01'>
                                        <polygon points='19.63,7.39,18.58,7.23,17.53,7.06,16.48,6.9,15.77,6.29,15.32,5.33,14.87,4.37,14.42,3.41,13.74,2.6,12.8,2.12,11.75,2.01,10.74,2.3,9.91,2.95,9.36,3.86,8.92,4.82,8.47,5.78,8.02,6.75,7.03,6.98,5.98,7.14,4.93,7.3,3.89,7.51,2.97,8.03,2.32,8.86,2.02,9.87,2.1,10.93,2.56,11.87,3.28,12.65,4.02,13.41,4.76,14.18,5.49,14.94,5.33,15.99,5.16,17.04,4.99,18.08,4.85,19.13,5.03,20.17,5.58,21.07,6.42,21.71,7.44,21.99,8.49,21.86,9.44,21.39,10.36,20.88,11.29,20.36,12.22,20.09,13.15,20.6,14.07,21.12,15,21.64,16,21.98,17.05,21.91,18,21.45,18.72,20.68,19.1,19.69,19.09,18.64,18.92,17.59,18.76,16.54,18.59,15.49,18.85,14.58,19.59,13.82,20.33,13.06,21.06,12.29,21.7,11.45,21.99,10.43,21.88,9.38,21.41,8.44,20.62,7.74' fill='#FF5757'></polygon>
                                    </svg>
                                </span>";
                            } else {
                                $ratingClassPercent .= "
                                <span class='star'>
                                    <svg xmlns='http://www.w3.org/2000/svg' height='18' width='18' viewBox='0 0 24 22' increment='0.01'>
                                        <linearGradient id='floatStar-" . $i . "' x1='0' x2='100%' y1='0' y2='0'>
                                            <stop offset='0' stop-color='#FF5757'></stop>
                                            <stop offset='" . $percent . "%' stop-color='#FF5757'></stop>
                                            <stop offset='" . $percent . "%' stop-color='#bdbdbd'></stop>
                                        </linearGradient>
                                        <polygon points='19.63,7.39,18.58,7.23,17.53,7.06,16.48,6.9,15.77,6.29,15.32,5.33,14.87,4.37,14.42,3.41,13.74,2.6,12.8,2.12,11.75,2.01,10.74,2.3,9.91,2.95,9.36,3.86,8.92,4.82,8.47,5.78,8.02,6.75,7.03,6.98,5.98,7.14,4.93,7.3,3.89,7.51,2.97,8.03,2.32,8.86,2.02,9.87,2.1,10.93,2.56,11.87,3.28,12.65,4.02,13.41,4.76,14.18,5.49,14.94,5.33,15.99,5.16,17.04,4.99,18.08,4.85,19.13,5.03,20.17,5.58,21.07,6.42,21.71,7.44,21.99,8.49,21.86,9.44,21.39,10.36,20.88,11.29,20.36,12.22,20.09,13.15,20.6,14.07,21.12,15,21.64,16,21.98,17.05,21.91,18,21.45,18.72,20.68,19.1,19.69,19.09,18.64,18.92,17.59,18.76,16.54,18.59,15.49,18.85,14.58,19.59,13.82,20.33,13.06,21.06,12.29,21.7,11.45,21.99,10.43,21.88,9.38,21.41,8.44,20.62,7.74' fill='url(#floatStar-" . $i . ")'></polygon>
                                    </svg>
                                </span>";
                            }
                        }
                        $ratings = $ratingClassFull . $ratingClassPercent;
                        echo $ratings;
                    ?>
                    </div>
                    <div class="review-author">
                        <?= $item->nickname?>
                    </div>
                </div>
                <div class="review-date">
                    <span class="review-value"><?= date('d.m.y', strtotime($item->created_at)) ?></span>
                </div>
            </div>
            <div class="review-item-content">
                <div class="review-additional-details">
                    <div class="review-age">
                        <span class="review-label">Age: </span>
                        <span class="review-value"><?= $item->age ?></span>
                    </div>
                    <div class="review-type">
                        <span class="review-label">Reason for purchase:</span>
                        <span class="review-value"><?= $item->type ?></span>
                    </div>
                </div>
                <div class="review-label">
                    <?= $item->title ?>
                </div>
                <div class="review-value">
                    <?= $item->detail; ?>
                </div>
                <div class="review-label">
                    <?php if($item->reply):?>
                        <label>Reply of admin:</label>
                        <?= $item->reply ?>
                    <?php endif;?>
                </div>
            </div>
        </li>
        <?php endforeach;?>
    </ol>
    <?php $star = ($count/count($data))*100/5 ?>
    <div class="review-total">
        <div class="star">
        <?php 
            $average = $count/count($data);
            $averageint = (int) ($average);
            $averagefloat = abs($average - $averageint);
            $percent = $averagefloat * 100;
            $ratingClassFull = '';
            $ratingClassEmpty = '';
            $ratingClassPercent='';
        for ($i = 1; $i <= 5; $i++) {
        if($i <= $averageint) {
                $ratingClassFull .= "<span  class='star'><svg xmlns='http://www.w3.org/2000/svg' height='18' width='18' viewBox='0 0 24 24' increment='0.01'>
                <linearGradient id='fm21u' x1='0' x2='100%' y1='0' y2='0'>
                    <stop offset='100%' stop-color='#FF5757'></stop> 
                    <stop offset='100%' stop-color='#bdbdbd'></stop>
                </linearGradient> <polygon points='19.63,7.39,18.58,7.23,17.53,7.06,16.48,6.9,15.77,6.29,15.32,5.33,14.87,4.37,14.42,3.41,13.74,2.6,12.8,2.12,11.75,2.01,10.74,2.3,9.91,2.95,9.36,3.86,8.92,4.82,8.47,5.78,8.02,6.75,7.03,6.98,5.98,7.14,4.93,7.3,3.89,7.51,2.97,8.03,2.32,8.86,2.02,9.87,2.1,10.93,2.56,11.87,3.28,12.65,4.02,13.41,4.76,14.18,5.49,14.94,5.33,15.99,5.16,17.04,4.99,18.08,4.85,19.13,5.03,20.17,5.58,21.07,6.42,21.71,7.44,21.99,8.49,21.86,9.44,21.39,10.36,20.88,11.29,20.36,12.22,20.09,13.15,20.6,14.07,21.12,15,21.64,16,21.98,17.05,21.91,18,21.45,18.72,20.68,19.1,19.69,19.09,18.64,18.92,17.59,18.76,16.54,18.59,15.49,18.85,14.58,19.59,13.82,20.33,13.06,21.06,12.29,21.7,11.45,21.99,10.43,21.88,9.38,21.41,8.44,20.62,7.74' fill='url(#fm21u)'></polygon></svg></span>";
        }else{
            if($percent != 0)
            {
                $ratingClassPercent = "<span  class='star'><svg xmlns='http://www.w3.org/2000/svg' height='18' width='18' viewBox='0 0 24 24' increment='0.01'>
            <linearGradient id='floatStar' x1='0' x2='100%' y1='0' y2='0'>
            
                <stop offset='".$percent."%' stop-color='#FF5757'></stop> 
                <stop offset='100%' stop-color='#bdbdbd'></stop>
            </linearGradient> 
            <polygon points='19.63,7.39,18.58,7.23,17.53,7.06,16.48,6.9,15.77,6.29,15.32,5.33,14.87,4.37,14.42,3.41,13.74,2.6,12.8,2.12,11.75,2.01,10.74,2.3,9.91,2.95,9.36,3.86,8.92,4.82,8.47,5.78,8.02,6.75,7.03,6.98,5.98,7.14,4.93,7.3,3.89,7.51,2.97,8.03,2.32,8.86,2.02,9.87,2.1,10.93,2.56,11.87,3.28,12.65,4.02,13.41,4.76,14.18,5.49,14.94,5.33,15.99,5.16,17.04,4.99,18.08,4.85,19.13,5.03,20.17,5.58,21.07,6.42,21.71,7.44,21.99,8.49,21.86,9.44,21.39,10.36,20.88,11.29,20.36,12.22,20.09,13.15,20.6,14.07,21.12,15,21.64,16,21.98,17.05,21.91,18,21.45,18.72,20.68,19.1,19.69,19.09,18.64,18.92,17.59,18.76,16.54,18.59,15.49,18.85,14.58,19.59,13.82,20.33,13.06,21.06,12.29,21.7,11.45,21.99,10.43,21.88,9.38,21.41,8.44,20.62,7.74' fill='url(#floatStar)'></polygon></svg></span>";
             $percent = 0;
            }else {
                $ratingClassEmpty .= "<span class='star'><svg xmlns='http://www.w3.org/2000/svg' height='18' width='18' viewBox='0 0 24 24' increment='0.01'>
            <linearGradient id='hxvf2a' x1='0' x2='100%' y1='0' y2='0'>
                <stop offset='0%' stop-color='#FF5757'></stop> 
                <stop offset='0%' stop-color='#bdbdbd'></stop>
            </linearGradient>
            <polygon points='19.63,7.39,18.58,7.23,17.53,7.06,16.48,6.9,15.77,6.29,15.32,5.33,14.87,4.37,14.42,3.41,13.74,2.6,12.8,2.12,11.75,2.01,10.74,2.3,9.91,2.95,9.36,3.86,8.92,4.82,8.47,5.78,8.02,6.75,7.03,6.98,5.98,7.14,4.93,7.3,3.89,7.51,2.97,8.03,2.32,8.86,2.02,9.87,2.1,10.93,2.56,11.87,3.28,12.65,4.02,13.41,4.76,14.18,5.49,14.94,5.33,15.99,5.16,17.04,4.99,18.08,4.85,19.13,5.03,20.17,5.58,21.07,6.42,21.71,7.44,21.99,8.49,21.86,9.44,21.39,10.36,20.88,11.29,20.36,12.22,20.09,13.15,20.6,14.07,21.12,15,21.64,16,21.98,17.05,21.91,18,21.45,18.72,20.68,19.1,19.69,19.09,18.64,18.92,17.59,18.76,16.54,18.59,15.49,18.85,14.58,19.59,13.82,20.33,13.06,21.06,12.29,21.7,11.45,21.99,10.43,21.88,9.38,21.41,8.44,20.62,7.74' fill='url(#hxvf2a)' style='color: #hxvf2a;'></polygon></svg></span>";
            }
        }
        }
            $ratings = $ratingClassFull . $ratingClassPercent . $ratingClassEmpty;
            echo $ratings;
        ?>
        </div>
        <div class="total">
            <span class="total-label">
                Reviews (<?=count($data)?>)
            </span>
        </div>
    </div>
</div>
<div class="action">
    <button id="load-more-reviews" data-offset="10" class="load-more">More</button>
</div>
<?php endif?>

<script>
    document.addEventListener("DOMContentLoaded", () => {
    jQuery(document).ready(function($) {
        var container = $('.review-items');
        var moreButton = $('#load-more-reviews');

        var offset = 10;


        function loadMoreReviews() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'load_more_reviews',
                    offset: offset,
                },
                success: function(response) {
                    if (response) {
                        container.append(response);
                        offset += 5;
                    }
                    if(offset > <?php echo count($data); ?>)
                    {
                        moreButton.hide(); 
                    }
                }
            });
        }

        // Sự kiện click vào nút "Load More"
        moreButton.on('click', function(e) {
            loadMoreReviews();
        });
    });
    })

</script>

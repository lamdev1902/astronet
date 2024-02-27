<?php 
    $postId = (get_the_ID()) ? get_the_ID() : "";
?>
<div id="reviews">
    <div class="reviews-main-title">
        <p>Customer Review</p>
    </div>
    <div class="reviews-content">
        <form action="<?php echo esc_attr( admin_url('admin-post.php') ); ?>" method="POST" id="reviewForm">
            <input type="hidden" name="action" value="customer_reviews">
            <input type="hidden" name="post_id" value="<?= $postId ?>">
            <input type="hidden" name="link" value="<?= get_permalink() ?>">
            <div class="reviews-rating">
                <div class="reviews-rating-radio">
                    <input type="radio" name="rate" value="5" class="rating-input" id="star_5">
                    <label for="star_5">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="13px" height="13px" viewBox="-0.5 1 13 1" xml:space="preserve"><g><path d="M11.5722 -0.0195813L8.03216 -0.533581L6.44816 -3.74158C6.35478 -3.90161 6.18344 -4.00002 5.99816 -4.00002C5.81288 -4.00002 5.64154 -3.90161 5.54816 -3.74158L3.96816 -0.533581L0.42816 -0.0195813C0.239768 0.00777626 0.0832573 0.13974 0.0244607 0.320799C-0.0343359 0.501859 0.0147843 0.700598 0.15116 0.833418L2.71316 3.33342L2.10716 6.85942C2.07488 7.04714 2.15206 7.23689 2.30621 7.34878C2.46035 7.46067 2.66468 7.47527 2.83316 7.38642L6.00016 5.71842L9.16716 7.38342C9.23858 7.42135 9.3183 7.44093 9.39916 7.44042C9.54665 7.44074 9.68676 7.37594 9.78201 7.26333C9.87726 7.15073 9.91794 7.00181 9.89316 6.85642L9.28716 3.33042L11.8492 0.830419C11.9855 0.697598 12.0347 0.498858 11.9759 0.317799C11.9171 0.136739 11.7606 0.004776 11.5722 -0.0225816L11.5722 -0.0195813Z"></path></g></svg>
                    </label>
                    <input type="radio" name="rate" value="4" class="rating-input" id="star_4">
                    <label for="star_4">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="13px" height="13px" viewBox="-0.5 1 13 1" xml:space="preserve"><g><path d="M11.5722 -0.0195813L8.03216 -0.533581L6.44816 -3.74158C6.35478 -3.90161 6.18344 -4.00002 5.99816 -4.00002C5.81288 -4.00002 5.64154 -3.90161 5.54816 -3.74158L3.96816 -0.533581L0.42816 -0.0195813C0.239768 0.00777626 0.0832573 0.13974 0.0244607 0.320799C-0.0343359 0.501859 0.0147843 0.700598 0.15116 0.833418L2.71316 3.33342L2.10716 6.85942C2.07488 7.04714 2.15206 7.23689 2.30621 7.34878C2.46035 7.46067 2.66468 7.47527 2.83316 7.38642L6.00016 5.71842L9.16716 7.38342C9.23858 7.42135 9.3183 7.44093 9.39916 7.44042C9.54665 7.44074 9.68676 7.37594 9.78201 7.26333C9.87726 7.15073 9.91794 7.00181 9.89316 6.85642L9.28716 3.33042L11.8492 0.830419C11.9855 0.697598 12.0347 0.498858 11.9759 0.317799C11.9171 0.136739 11.7606 0.004776 11.5722 -0.0225816L11.5722 -0.0195813Z"></path></g></svg>
                    </label>
                    <input type="radio" name="rate" value="3" class="rating-input" id="star_3">
                    <label for="star_3">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="13px" height="13px" viewBox="-0.5 1 13 1" xml:space="preserve"><g><path d="M11.5722 -0.0195813L8.03216 -0.533581L6.44816 -3.74158C6.35478 -3.90161 6.18344 -4.00002 5.99816 -4.00002C5.81288 -4.00002 5.64154 -3.90161 5.54816 -3.74158L3.96816 -0.533581L0.42816 -0.0195813C0.239768 0.00777626 0.0832573 0.13974 0.0244607 0.320799C-0.0343359 0.501859 0.0147843 0.700598 0.15116 0.833418L2.71316 3.33342L2.10716 6.85942C2.07488 7.04714 2.15206 7.23689 2.30621 7.34878C2.46035 7.46067 2.66468 7.47527 2.83316 7.38642L6.00016 5.71842L9.16716 7.38342C9.23858 7.42135 9.3183 7.44093 9.39916 7.44042C9.54665 7.44074 9.68676 7.37594 9.78201 7.26333C9.87726 7.15073 9.91794 7.00181 9.89316 6.85642L9.28716 3.33042L11.8492 0.830419C11.9855 0.697598 12.0347 0.498858 11.9759 0.317799C11.9171 0.136739 11.7606 0.004776 11.5722 -0.0225816L11.5722 -0.0195813Z"></path></g></svg>
                    </label>
                    <input type="radio" name="rate" value="2" class="rating-input" id="star_2">
                    <label for="star_2">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="13px" height="13px" viewBox="-0.5 1 13 1" xml:space="preserve"><g><path d="M11.5722 -0.0195813L8.03216 -0.533581L6.44816 -3.74158C6.35478 -3.90161 6.18344 -4.00002 5.99816 -4.00002C5.81288 -4.00002 5.64154 -3.90161 5.54816 -3.74158L3.96816 -0.533581L0.42816 -0.0195813C0.239768 0.00777626 0.0832573 0.13974 0.0244607 0.320799C-0.0343359 0.501859 0.0147843 0.700598 0.15116 0.833418L2.71316 3.33342L2.10716 6.85942C2.07488 7.04714 2.15206 7.23689 2.30621 7.34878C2.46035 7.46067 2.66468 7.47527 2.83316 7.38642L6.00016 5.71842L9.16716 7.38342C9.23858 7.42135 9.3183 7.44093 9.39916 7.44042C9.54665 7.44074 9.68676 7.37594 9.78201 7.26333C9.87726 7.15073 9.91794 7.00181 9.89316 6.85642L9.28716 3.33042L11.8492 0.830419C11.9855 0.697598 12.0347 0.498858 11.9759 0.317799C11.9171 0.136739 11.7606 0.004776 11.5722 -0.0225816L11.5722 -0.0195813Z"></path></g></svg>
                    </label>
                    <input type="radio" name="rate" value="1" class="rating-input" id="star_1">
                    <label for="star_1">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="13px" height="13px" viewBox="-0.5 1 13 1" xml:space="preserve"><g><path d="M11.5722 -0.0195813L8.03216 -0.533581L6.44816 -3.74158C6.35478 -3.90161 6.18344 -4.00002 5.99816 -4.00002C5.81288 -4.00002 5.64154 -3.90161 5.54816 -3.74158L3.96816 -0.533581L0.42816 -0.0195813C0.239768 0.00777626 0.0832573 0.13974 0.0244607 0.320799C-0.0343359 0.501859 0.0147843 0.700598 0.15116 0.833418L2.71316 3.33342L2.10716 6.85942C2.07488 7.04714 2.15206 7.23689 2.30621 7.34878C2.46035 7.46067 2.66468 7.47527 2.83316 7.38642L6.00016 5.71842L9.16716 7.38342C9.23858 7.42135 9.3183 7.44093 9.39916 7.44042C9.54665 7.44074 9.68676 7.37594 9.78201 7.26333C9.87726 7.15073 9.91794 7.00181 9.89316 6.85642L9.28716 3.33042L11.8492 0.830419C11.9855 0.697598 12.0347 0.498858 11.9759 0.317799C11.9171 0.136739 11.7606 0.004776 11.5722 -0.0225816L11.5722 -0.0195813Z"></path></g></svg>
                    </label>
                </div>
                <div class="reviews-rating-text">
                    <p>You are reviewing CBD natural extract PREMIUM oil 10%</p>
                </div>
            </div>
            <div class="reviews-form">
                <div class="reviews-nickname">
                    <label for="nickname_field">Your name: *</label>
                    <div class="control">
                        <input type="text" class="field-input" id="nickname_field" name="nickname" >
                        <p class="error nickname-error"></p>
                    </div>
                </div>
                <div class="reviews-title">
                    <label for="title_field">Email: *</label>
                    <div class="control">
                        <input type="text" class="field-input" id="title_field" name="title">
                        <p class="error title-error"></p>
                    </div>
                </div>
                <div class="reviews-feedback">
                    <label for="feedback_field">Your feedback: *</label>
                    <div class="control">
                        <textarea name="feedback" class="field-input" id="feedback_field" cols="30" rows="10"></textarea>
                        <p class="error feedback-error"></p>
                    </div>
                </div>
            <div class="reviews-action">
                <div class="g-recaptcha" data-sitekey="6LeRDn0pAAAAANGR4iPruRTcrrnO1tLaMFLfuokF"></div>
                <button id="btnReview" disabled type="submit"  class="primary">
                    Send
                </button>
            </div>
        </form>
    </div>
</div>

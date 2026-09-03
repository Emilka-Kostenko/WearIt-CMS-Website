<?php
/**
 * Template Name: Contact Page
 */
?>
<?php get_header(); ?>
<main>
    <section class="contact" id="contact">
        <div class="contact-container">
            <div class="contact-header">
                <p class="contact__eyebrow">
                    <?php
                    $eyebrow = get_field( 'contact_eyebrow' );
                    echo $eyebrow ? esc_html( $eyebrow ) : 'WearIt';
                    ?>
                </p>
                <h1 class="contact__heading">
                <?php
                $heading = get_field( 'contact_heading' );
                echo $heading ? esc_html( $heading ) : 'Get in Touch';
                ?>
                </h1>
            </div>
            <div class="contact__image-wrap"> <!-- im guessing this one aint wrap but i shall figure that out later bleh--->
                <?php
                $contact_image = get_field( 'contact_image' );
                if ( $contact_image ) :
                ?>
                    <img
                        class="contact__image"
                        src="<?php echo esc_url( $contact_image['url'] ); ?>"
                        alt="<?php echo esc_attr( $contact_image['alt'] ); ?>"
                    />
                <?php else : ?> <!-- hopefully the hero placeholder thingy works bleh-->
                    <img
                        class="contact__image"
                        src="<?php echo esc_url( get_template_directory_uri() . '/assets/hero-placeholder.jpg' ); ?>" 
                        alt="Model in dark streetwear editorial"
                    />
                <?php endif; ?>
                <div class="contact__image-fade" aria-hidden="true"></div>
            </div>
        </div>
    </section>

    <section class="contact__form">
        <section class="contact__info">
            <div class="contact__into-top">
                <h2 class="contact__info_heading">
                    <span class="contact__info-heading-line1">
                        <?php
                        $line1 = get_field( 'contact__info-heading-line1' );
                        echo $line1 ? esc_html( $line1 ) : 'We read';
                        ?>
                    </span>
                    <span class="contact__info-heading-line2">
                        <?php
                        $line2 = get_field( 'contact__info-heading-line2' );
                        echo $line2 ? esc_html( $line2 ) : 'Everything';
                        ?>
                    </span>
                </h2>
                <p class="contact__info-description">
                    <?php
                    $description = get_field( 'contact__info-description' );
                    echo $description ? esc_html( $description ) : 'Questions about sizing, a drop you missed, a wholesale inquiry, or just something you want to say — send it through. We respond to every message within 48 hours, usually faster.';
                    ?>
                </p>
            </div>
            <div class="contact__info-middle">
                <div class="contact__info-row">
                    <div class="contact__info-mail"> <!-- im guessing this one aint wrap but i shall figure that out later bleh--->
                        <?php
                        $contact_infoimg = get_field( 'contact_infoimg' );
                        if ( $contact_infoimg ) :
                        ?>
                            <img
                                class="contact__info-mail-image"
                                src="<?php echo esc_url( $contact_infoimg['url'] ); ?>"
                                alt="<?php echo esc_attr( $contact_infoimg['alt'] ); ?>"
                            />
                        <?php else : ?> <!-- hopefully the hero placeholder thingy works bleh-->
                            <img
                                class="contact__info-mail-image"
                                src="<?php echo esc_url( get_template_directory_uri() . '/assets/hero-placeholder.jpg' ); ?>" 
                                alt="Model in dark streetwear editorial"
                            />
                        <?php endif; ?>
                        <div class="contact__image-fade" aria-hidden="true"></div>
                    </div>
                    <div>
                        <p class="contact__info-mail-text1">
                            <?php
                            $mail_text1 = get_field( 'contact__info-mail-text1' );
                            echo $mail_text1 ? esc_html( $mail_text1 ) : 'email';
                            ?>
                        </p>
                        <p class="contact__info-mail-text2">
                            <?php
                            $mail_text2 = get_field( 'contact__info-mail-text2' );
                            echo $mail_text2 ? esc_html( $mail_text2 ) : 'hello@wearit.com';
                            ?>
                        </p>
                    </div>
                </div>
                <div class="contact__info-row">
                    <div class="contact__info-instragram"> <!-- im guessing this one aint wrap but i shall figure that out later bleh--->
                        <?php
                        $contact_infoimg = get_field( 'contact_infoimg' );
                        if ( $contact_infoimg ) :
                        ?>
                            <img
                                class="contact__info-intsragram-image"
                                src="<?php echo esc_url( $contact_infoimg['url'] ); ?>"
                                alt="<?php echo esc_attr( $contact_infoimg['alt'] ); ?>"
                            />
                        <?php else : ?> <!-- hopefully the hero placeholder thingy works bleh-->
                            <img
                                class="contact__info-intsragram-image"
                                src="<?php echo esc_url( get_template_directory_uri() . '/assets/hero-placeholder.jpg' ); ?>" 
                                alt="Model in dark streetwear editorial"
                            />
                        <?php endif; ?>
                        <div class="contact__image-fade" aria-hidden="true"></div>
                    </div>
                    <div>
                        <p class="contact__info-instagram-text1">
                            <?php
                            $instragram_text1 = get_field( 'contact__info-instagram-text1' );
                            echo $instragram_text1 ? esc_html( $instragram_text1 ) : 'instagram';
                            ?>
                        </p>
                        <p class="contact__info-instagram-text2">
                            <?php
                            $instragram_text2 = get_field( 'contact__info-instagram-text2' );
                            echo $instragram_text2 ? esc_html( $instragram_text2 ) : '@wearit.official';
                            ?>
                        </p>
                    </div>
                </div>
                <div class="contact__info-row">
                    <div class="contact__info-location"> <!-- im guessing this one aint wrap but i shall figure that out later bleh---> <!-- we might need to change the img to some svg or something for the location icon but we can figure that out later bleh-->
                        <?php
                        $contact_infoimg = get_field( 'contact_infoimg' );
                        if ( $contact_infoimg ) :
                        ?>
                            <img
                                class="contact__info-location-image"
                                src="<?php echo esc_url( $contact_infoimg['url'] ); ?>"
                                alt="<?php echo esc_attr( $contact_infoimg['alt'] ); ?>"
                            />
                        <?php else : ?> <!-- hopefully the hero placeholder thingy works bleh-->
                            <img
                                class="contact__info-location-image"
                                src="<?php echo esc_url( get_template_directory_uri() . '/assets/hero-placeholder.jpg' ); ?>" 
                                alt="Model in dark streetwear editorial"
                            />
                        <?php endif; ?>
                        <div class="contact__image-fade" aria-hidden="true"></div>
                    </div>
                    <div>
                        <p class="contact__info-location-text1">
                            <?php
                            $location_text1 = get_field( 'contact__info-location-text1' );
                            echo $location_text1 ? esc_html( $location_text1 ) : 'address';
                            ?>
                        </p>
                        <p class="contact__info-location-text2">
                            <?php
                            $location_text2 = get_field( 'contact__info-location-text2' );
                            echo $location_text2 ? esc_html( $location_text2 ) : 'East London, UK';
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="contact__info-bottom">
                <p class="contact__info-bottom-text">
                    <?php
                    $bottom_text = get_field( 'contact__info-bottom-text' );
                    echo $bottom_text ? esc_html( $bottom_text ) : 'For returns and order issues, include your order number in the subject line. For press and wholesale, mark your subject accordingly — those go to a separate inbox.';
                    ?>
                </p>
            </div>
        </section>
        <section class="contact__filler">
            <form class="contact__filler-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"> <!-- no handler hooked up yet, so this just posts into the void for now bleh -->
                <input type="hidden" name="action" value="wearit_contact" />
                <?php wp_nonce_field( 'wearit_contact_nonce', 'wearit_contact_nonce' ); ?>

                <div class="contact__filler-row"> <!-- name and email sit next to each other bleh -->
                    <div class="contact__name">
                        <label for="name_input" class="contact__name-label">
                            <?php
                            $name_label = get_field( 'contact__name-label' );
                            echo $name_label ? esc_html( $name_label ) : 'Name';
                            ?>
                        </label>
                        <input type="text" id="name_input" name="contact_name" class="contact__name-input contact__input" placeholder="<?php
                        $name_input_placeholder = get_field( 'contact__name-input' );
                        echo $name_input_placeholder ? esc_attr( $name_input_placeholder ) : 'Your name';
                        ?>" required />
                    </div>
                    <div class="contact__email">
                        <label for="email_input" class="contact__email-label">
                            <?php
                            $email_label = get_field( 'contact__email-label' );
                            echo $email_label ? esc_html( $email_label ) : 'Email';
                            ?>
                        </label>
                        <input type="email" id="email_input" name="contact_email" class="contact__email-input contact__input" placeholder="<?php
                        $email_input_placeholder = get_field( 'contact__email-input' );
                        echo $email_input_placeholder ? esc_attr( $email_input_placeholder ) : 'you@email.com';
                        ?>" required /> <!-- took out that example.com pattern, it was blocking every real email bleh -->
                    </div>
                </div>

                <div class="contact__subject">
                    <label for="subject_input" class="contact__subject-label">
                        <?php
                        $subject_label = get_field( 'contact__subject-label' );
                        echo $subject_label ? esc_html( $subject_label ) : 'Subject';
                        ?>
                    </label>
                    <input type="text" id="subject_input" name="contact_subject" class="contact__subject-input contact__input" placeholder="<?php
                    $subject_input_placeholder = get_field( 'contact__subject-input' );
                    echo $subject_input_placeholder ? esc_attr( $subject_input_placeholder ) : 'What is this about?';
                    ?>" required />
                </div>

                <div class="contact__message">
                    <label for="message_input" class="contact__message-label">
                        <?php
                        $message_label = get_field( 'contact__message-label' );
                        echo $message_label ? esc_html( $message_label ) : 'Message';
                        ?>
                    </label>
                    <textarea id="message_input" name="contact_message" class="contact__message-input contact__input" rows="6" placeholder="<?php
                    $message_input_placeholder = get_field( 'contact__message-input' );
                    echo $message_input_placeholder ? esc_attr( $message_input_placeholder ) : 'Write your message here...';
                    ?>" required></textarea>
                </div>

                <div class="contact__filler-bottom">
                    <p class="contact__filler-note">
                        <?php
                        $filler_note = get_field( 'contact__filler-note' );
                        echo $filler_note ? esc_html( $filler_note ) : 'We respond within 48 hours.';
                        ?>
                    </p>
                    <button type="submit" class="contact__submit">
                        <?php
                        $submit_text = get_field( 'contact__submit-text' );
                        echo $submit_text ? esc_html( $submit_text ) : 'Send Message';
                        ?>
                        <span class="contact__submit-arrow" aria-hidden="true">&rarr;</span>
                    </button>
                </div>
            </form>
        </section>
    </section>

</main>

<?php get_footer(); ?>
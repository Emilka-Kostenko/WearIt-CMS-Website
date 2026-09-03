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
                <div>
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
                <div>
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
                            echo $instragram_text1 ? esc_html( $instragram_text1 ) : 'email';
                            ?>
                        </p>
                        <p class="contact__info-instagram-text2">
                            <?php
                            $instragram_text2 = get_field( 'contact__info-instagram-text2' );
                            echo $instragram_text2 ? esc_html( $instragram_text2 ) : 'hello@wearit.com';
                            ?>
                        </p>
                    </div>
                </div>
                <div>
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
            <div >
                <div class="contact__name">
                    <label for="name_input" class="contact__name-label">
                        <?php
                        $name_label = get_field( 'contact__name-label' );
                        echo $name_label ? esc_html( $name_label ) : 'Name';
                        ?>
                    </label>
                    <input type="text" id="name_input" class="contact__name-input" placeholder="
                    <?php 
                    $name_input_placeholder = get_field('contact__name-input');
                    echo $name_input_placeholder ? esc_html( $name_input_placeholder ) :'Name';
                    ?>
                    " required />
                </div>
                <div>
                    <label for="email_input">
                        <?php
                        $email_label = get_field( 'contact__email-label' );
                        echo $email_label ? esc_html( $email_label ) : 'Email';
                        ?>
                    </label>
                    <input type="email" id="email_input" placeholder="Your email:" pattern=".+@example\.com"  required />
                </div>
                <div>
                    <label for="subject_input">
                        <?php
                        $subject_label = get_field( 'contact__subject-label' );
                        echo $subject_label ? esc_html( $subject_label ) : 'Subject';
                        ?>
                    </label>
                    <input type="text" id="subject_input" placeholder="Subject:" required />
                </div>
                <div>
                    <label for="message_input">
                        <?php
                        $message_label = get_field( 'contact__message-label' );
                        echo $message_label ? esc_html( $message_label ) : 'Message';
                        ?>
                    </label>
                    <textarea id="message_input" placeholder="
                    <?php 
                    ?>
                    " required></textarea>
                </div>
                <div>
                    <p>
                        <?php ?>
                    </p>
                </div>
            </div>
            
        </section>
    </section>

</main>

<?php get_footer(); ?>
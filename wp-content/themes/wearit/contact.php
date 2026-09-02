<main>
    <section class="contact" id="contact">
        <div class="contact-container">
            <div>
                <p class="contact__eyebrow">
                    <?php
                    $eyebrow = get_field( 'contact_eyebrow' );
                    echo $eyebrow ? esc_html( $eyebrow ) : 'Wear It.';
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
                <P2 class="contact__info-description">
                    <?php
                    $description = get_field( 'contact__info-description' );
                    echo $description ? esc_html( $description ) : 'Questions about sizing, a drop you missed, a wholesale inquiry, or just something you want to say — send it through. We respond to every message within 48 hours, usually faster.';
                    ?>
                </P2>
            </div>
            <div class="contact__info-middle">

            </div>
        </section>
        <section class="contact__fillin">
            
        </section>
    </section>





</main>
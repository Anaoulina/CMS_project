<div class="lastudio-kit-settings-page lastudio-kit-settings-page__integratios">
  <div class="cx-vui-title cx-vui-title--divider" v-html="'<?php esc_attr_e( 'General', 'lastudio-kit' ); ?>'"></div>
  <div class="cx-vui-panel">
    <cx-vui-switcher name="template-cache"
      label="<?php esc_attr_e( 'Template cache', 'lastudio-kit' ); ?>"
      description="<?php esc_attr_e( 'Enable or disable template cache', 'lastudio-kit' ); ?>"
      :wrapper-css="[ 'equalwidth' ]"
      return-true="true"
      return-false="false"
      v-model="pageOptions['template-cache'].value">
    </cx-vui-switcher>
    <cx-vui-switcher name="svg-uploads"
      label="<?php esc_attr_e( 'SVG images upload status', 'lastudio-kit' ); ?>"
      description="<?php esc_attr_e( 'Enable or disable SVG images uploading', 'lastudio-kit' ); ?>"
      :wrapper-css="[ 'equalwidth' ]"
      return-true="enabled"
      return-false="disabled"
      v-model="pageOptions['svg-uploads'].value">
    </cx-vui-switcher>

      <cx-vui-switcher name="disable-gutenberg-block"
      label="<?php esc_attr_e( 'Disable Gutenberg Block CSS', 'lastudio-kit' ); ?>"
      description="<?php esc_attr_e( 'Enable or disable the Gutenberg Block CSS', 'lastudio-kit' ); ?>"
      :wrapper-css="[ 'equalwidth' ]"
      return-true="enabled"
      return-false="disabled"
      v-model="pageOptions['disable-gutenberg-block'].value">
    </cx-vui-switcher>
  </div>
  <div class="cx-vui-title cx-vui-title--divider" v-html="'<?php esc_attr_e( 'Google Maps', 'lastudio-kit' ); ?>'"></div>
  <div class="cx-vui-panel">
    <cx-vui-input
      name="google-map-api-key"
      label="<?php esc_attr_e( 'Google Map API Key', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Create own API key, more info %1$shere%2$s', 'lastudio-kit'),
          esc_html('<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions.gmap_api_key.value"></cx-vui-input>
    <cx-vui-input
      name="google-map-backemd-api-key"
      label="<?php esc_attr_e( 'Google Map API Key (Backend)', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Create own API key, more info %1$shere%2$s', 'lastudio-kit'),
          esc_html('<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions.gmap_backend_api_key.value"></cx-vui-input>
    <cx-vui-switcher
      name="google-map-disable-api-js"
      label="<?php esc_attr_e( 'Disable Google Maps API JS file', 'lastudio-kit' ); ?>"
      description="<?php esc_attr_e( 'Disable Google Maps API JS file, if it already included by another plugin or theme', 'lastudio-kit' ); ?>"
      :wrapper-css="[ 'equalwidth' ]"
      return-true="true"
      return-false="false"
      v-model="pageOptions.disable_gmap_api_js.value">
    </cx-vui-switcher>
  </div>
  <div class="cx-vui-title cx-vui-title--divider" v-html="'<?php esc_attr_e( 'reCAPTCHA (v3)', 'lastudio-kit' ); ?>'"></div>
  <div class="cx-vui-panel">
    <cx-vui-input
      name="recaptcha-site-key"
      label="<?php esc_attr_e( 'Site key', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Create own Site key, more info %1$shere%2$s', 'lastudio-kit'),
          esc_html('<a href="https://www.google.com/recaptcha/admin/create" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions.recaptchav3.value.site_key"></cx-vui-input>
    <cx-vui-input
      name="recaptcha-secret-key"
      label="<?php esc_attr_e( 'Secret key', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Create own Site key, more info %1$shere%2$s', 'lastudio-kit'),
          esc_html('<a href="https://www.google.com/recaptcha/admin/create" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions.recaptchav3.value.secret_key"></cx-vui-input>
    <cx-vui-switcher
      name="recaptcha-disable-api-js"
      label="<?php esc_attr_e( 'Disable reCAPTCHA v3 JS file', 'lastudio-kit' ); ?>"
      description="<?php esc_attr_e( 'Disable reCAPTCHA v3 JS file, if it already included by another plugin or theme', 'lastudio-kit' ); ?>"
      :wrapper-css="[ 'equalwidth' ]"
      return-true="true"
      return-false="false"
      v-model="pageOptions.recaptchav3.value.disable">
    </cx-vui-switcher>
  </div>
  <div
    class="cx-vui-title cx-vui-title--divider"
    v-html="'<?php esc_attr_e( 'MailChimp', 'lastudio-kit' ); ?>'"></div>
  <div class="cx-vui-panel">
    <cx-vui-input
      name="mailchimp-api-key"
      label="<?php esc_attr_e( 'MailChimp API key', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Input your MailChimp API key %1$sAbout API Keys%2$s', 'lastudio-kit'),
          esc_html('<a href="https://mailchimp.com/help/about-api-keys/" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions['mailchimp-api-key'].value"></cx-vui-input>
    <cx-vui-input
      name="mailchimp-list-id"
      label="<?php esc_attr_e( 'MailChimp list ID', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Input MailChimp list ID %1$sAbout Mailchimp List ID%2$s', 'lastudio-kit'),
          esc_html('<a href="https://mailchimp.com/help/find-audience-id/" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions['mailchimp-list-id'].value"></cx-vui-input>
    <cx-vui-switcher
      name="mailchimp-double-opt-in"
      label="<?php esc_attr_e( 'Double opt-in', 'lastudio-kit' ); ?>"
      description="<?php esc_attr_e( 'Send contacts an opt-in confirmation email when they subscribe to your list.', 'lastudio-kit' ); ?>"
      :wrapper-css="[ 'equalwidth' ]"
      return-true="true"
      return-false="false"
      v-model="pageOptions['mailchimp-double-opt-in'].value">
    </cx-vui-switcher>
  </div>
  <div
    class="cx-vui-title cx-vui-title--divider"
    v-html="'<?php esc_attr_e( 'Instagram', 'lastudio-kit' ); ?>'"></div>
  <div class="cx-vui-panel">
    <cx-vui-input
      name="insta-access-token"
      label="<?php esc_attr_e( 'Access Token', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Read more about how to get Instagram Access Token %1$shere%2$s', 'lastudio-kit'),
          esc_html('<a href="https://la-studioweb.com/tip-trick/how-to-get-instagram-access-token/" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions.insta_access_token.value"></cx-vui-input>
    <cx-vui-input
      name="insta-business-access-token"
      label="<?php esc_attr_e( 'Business Access Token', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Read more about how to get Business Instagram Access Token %1$shere%2$s', 'lastudio-kit'),
          esc_html('<a href="https://la-studioweb.com/tip-trick/lastudiokit-how-to-display-instagram-tagged-photos/" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions.insta_business_access_token.value"></cx-vui-input>
    <cx-vui-input
      name="insta-business-user-id"
      label="<?php esc_attr_e( 'Business User ID', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Read more about how to get Business User ID %1$shere%2$s', 'lastudio-kit'),
          esc_html('<a href="https://la-studioweb.com/tip-trick/lastudiokit-how-to-display-instagram-tagged-photos/" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions.insta_business_user_id.value"></cx-vui-input>
  </div>
  <div
    class="cx-vui-title cx-vui-title--divider"
    v-html="'<?php esc_attr_e( 'Weatherbit.io API', 'lastudio-kit' ); ?>'"></div>
  <div class="cx-vui-panel">
    <cx-vui-input
      name="weatherstack-api-key"
      label="<?php esc_attr_e( 'Weatherbit.io API Key', 'lastudio-kit' ); ?>"
      description="<?php
      echo sprintf(
      /* translators: %s is replaced with "string" */
        esc_attr__('Create own Weatherbit.io API key, more info %1$shere%2$s', 'lastudio-kit'),
        esc_html('<a href="https://www.weatherbit.io/" target="_blank">'),
        '</a>'
      );
      ?>"
      :wrapper-css="[ 'equalwidth' ]"
      size="fullwidth"
      v-model="pageOptions.weather_api_key.value"></cx-vui-input>
  </div>
</div>

<?php

if ( ! class_exists('Lkn_Puc_StateStore', false) ) {
    class Lkn_Puc_StateStore {
        /**
         * @var int Last update check timestamp.
         */
        protected $lastCheck = 0;

        /**
         * @var string Version number.
         */
        protected $checkedVersion = '';

        /**
         * @var Lkn_Puc_Update|null Cached update.
         */
        protected $update = null;

        /**
         * @var string Site option name.
         */
        private $optionName = '';

        /**
         * @var bool Whether we've already tried to load the state from the database.
         */
        private $isLoaded = false;

        public function __construct($optionName) {
            $this->optionName = $optionName;
        }

        /**
         * Get time elapsed since the last update check.
         *
         * If there are no recorded update checks, this method returns a large arbitrary number
         * (i.e. time since the Unix epoch).
         *
         * @return int Elapsed time in seconds.
         */
        public function timeSinceLastCheck() {
            $this->lazyLoad();
            return time() - $this->lastCheck;
        }

        /**
         * @return int
         */
        public function getLastCheck() {
            $this->lazyLoad();
            return $this->lastCheck;
        }

        /**
         * Set the time of the last update check to the current timestamp.
         *
         * @return $this
         */
        public function setLastCheckToNow() {
            $this->lazyLoad();
            $this->lastCheck = time();
            return $this;
        }

        /**
         * @return Lkn_Puc_Update|null
         */
        public function getUpdate() {
            $this->lazyLoad();
            return $this->update;
        }

        /**
         * @param Lkn_Puc_Update|null $update
         * @return $this
         */
        public function setUpdate(Lkn_Puc_Update $update = null) {
            $this->lazyLoad();
            $this->update = $update;
            return $this;
        }

        /**
         * @return string
         */
        public function getCheckedVersion() {
            $this->lazyLoad();
            return $this->checkedVersion;
        }

        /**
         * @param string $version
         * @return $this
         */
        public function setCheckedVersion($version) {
            $this->lazyLoad();
            $this->checkedVersion = (string) $version;
            return $this;
        }

        /**
         * Get translation updates.
         *
         * @return array
         */
        public function getTranslations() {
            $this->lazyLoad();
            if ( isset($this->update, $this->update->translations) ) {
                return $this->update->translations;
            }
            return array();
        }

        /**
         * Set translation updates.
         *
         * @param array $translationUpdates
         */
        public function setTranslations($translationUpdates): void {
            $this->lazyLoad();
            if ( isset($this->update) ) {
                $this->update->translations = $translationUpdates;
                $this->save();
            }
        }

        /**
         * Saves the updated state of the plugin on the database
         */
        public function save(): void {
            $state = new stdClass();

            $state->lastCheck = $this->lastCheck;
            $state->checkedVersion = $this->checkedVersion;

            if ( isset($this->update)) {
                $state->update = $this->update->toStdClass();

                $updateClass = get_class($this->update);
                $state->updateClass = $updateClass;
                $prefix = $this->getLibPrefix();
                if ( Lkn_Puc_Utils::startsWith($updateClass, $prefix) ) {
                    $state->updateBaseClass = substr($updateClass, strlen($prefix));
                }
            }

            update_site_option($this->optionName, $state);
            $this->isLoaded = true;
        }

        /**
         * Checks if the database already has a state
         * 
         * @return $this
         */
        public function lazyLoad() {
            if ( ! $this->isLoaded ) {
                $this->load();
            }
            return $this;
        }

        /**
         * Load the state version
         */
        protected function load(): void {
            $this->isLoaded = true;

            $state = get_site_option($this->optionName, null);

            if ( ! is_object($state) ) {
                $this->lastCheck = 0;
                $this->checkedVersion = '';
                $this->update = null;
                return;
            }

            $this->lastCheck = (int) (Lkn_Puc_Utils::get($state, 'lastCheck', 0));
            $this->checkedVersion = Lkn_Puc_Utils::get($state, 'checkedVersion', '');
            $this->update = null;

            if ( isset($state->update) ) {
                //This mess is due to the fact that the want the update class from this version
                //of the library, not the version that saved the update.

                $updateClass = null;
                if ( isset($state->updateBaseClass) ) {
                    $updateClass = $this->getLibPrefix() . $state->updateBaseClass;
                } else {
                    if ( isset($state->updateClass) && class_exists($state->updateClass) ) {
                        $updateClass = $state->updateClass;
                    }
                }

                if ( null !== $updateClass ) {
                    $this->update = call_user_func(array($updateClass, 'fromObject'), $state->update);
                }
            }
        }

        /**
         * Delete the option name from database
         */
        public function delete(): void {
            delete_site_option($this->optionName);

            $this->lastCheck = 0;
            $this->checkedVersion = '';
            $this->update = null;
        }

        private function getLibPrefix() {
            $parts = explode('_', __CLASS__, 3);
            return $parts[0] . '_' . $parts[1] . '_';
        }
    }
}

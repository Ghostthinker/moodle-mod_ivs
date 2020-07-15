<?php

namespace mod_ivs\license;

interface ILicenseController {

    public function generateInstanceId();

    public function getInstanceId();

    public function hasActiveLicense($context = null);

    public function getActiveLicense($context = null);

    public function coreRegister($instance_id);

    public function activateCourseLicense($course_id, $license_id);

    public function getStatus();

    public function getLicenseType($license);

    public function getCDNSource($license_id);

    public function releaseCourseLicense($course_id, $license_id);

    public function getCourseLicenses($status, $reset = false);

    public function getInstanceLicenses($status, $reset = false);

    public function getCourseLicenseOptions($course_licenses);

    public function sendUsage();

}

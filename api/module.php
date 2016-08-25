<?php namespace pineapple;

class HackRF extends Module
{
    public function route()
    {
        switch ($this->request->action) {
            case 'getHackRF_Info':
                $this->getHackRF_Info();
                break;
            case 'hackrf_Install':
                $this->hackrf_Install();
                break;
            case 'hackrf_Uninstall':
                $this->hackrf_Uninstall();
                break;
            case 'hackrf_Checker':
                $this->hackrf_Checker();
                break;
            case 'doHackRF_Transfer':
                $this->doHackRF_Transfer();
                break;
            case 'doHackRF_Stop':
                $this->doHackRF_Stop();
                break;
            case 'getHackRF_Log':
                $this->getHackRF_Log();
                break;
        }
    }

    private function getHackRF_Info()
    {
        exec('hackrf_info', $message);
        $message = implode("\n", $message);

        if ($message == "No HackRF boards found.") {
            $this->response = array("foundBoard" => false);
        } else if ($this->checkDependency('hackrf_info') == false) {
            $this->response = array("foundBoard" => false);
        } else {
            $this->response = array("foundBoard" => true,
                                    "availableHackRFs" => $message);
        }
    }

    private function hackrf_Checker()
    {
        if ($this->checkDependency('hackrf_info')) {
            $this->response = array("installed" => true);
        } else {
            $this->response = array("installed" => false);
        }
    }

    private function hackrf_Install()
    {
        if ($this->getDevice() == 'tetra') {
            $this->execBackground('opkg update && opkg install hackrf-mini');    
        } else {
            $this->execBackground('opkg update && opkg install hackrf-mini --dest sd');
        }

        $this->response = array("installing" => true);
    }

    private function hackrf_Uninstall()
    {
        exec('opkg remove hackrf-mini');
        $this->response = array("success" => true);
    }

    private function doHackRF_Transfer()
    {
        $mode       = $this->request->mode;
        $sampleRate = $this->request->sampleRate;
        $centerFreq = $this->request->centerFreq;
        $filename   = $this->request->filename;
        $amp        = $this->request->amp;
        $antpower   = $this->request->antpower;

        if (!$sampleRate) {
            $this->response = array("success" => false, "sampleRateError" => true);
        } else {
            $this->response = array("success" => true);
        }

        if ($mode == "rx") {
            $mode = "-r";
        } else if ($mode == "tx") {
            $mode = "-t";
        }

        if (strpos($sampleRate, 'K') == true) {
            $sampleRate = str_replace('K', '0000', $sampleRate);
        } else if (strpos($sampleRate, 'M') == true) {
            $sampleRate = str_replace('M', '000000', $sampleRate);
        } else if (strpos($sampleRate, 'k') == true) {
            $sampleRate = str_replace('k', '0000', $sampleRate);
        } else if (strpos($sampleRate, 'm') == true) {
            $sampleRate = str_replace('m', '000000', $sampleRate);
        }

        if (strpos($centerFreq, 'KHz') == true) {
            $centerFreq = str_replace('KHz', '0000', $centerFreq);
        } else if (strpos($centerFreq, 'MHz') == true) {
            $centerFreq = str_replace('MHz', '000000', $centerFreq);
        } else if (strpos($centerFreq, 'khz') == true) {
            $centerFreq = str_replace('khz', '0000', $centerFreq);
        } else if (strpos($centerFreq, 'mhz') == true) {
            $centerFreq = str_replace('mhz', '000000', $centerFreq);
        }

        $command = "hackrf_transfer $mode $filename -f $centerFreq -s $sampleRate";

        if ($amp) {
            $command = $command . " -a";
        }

        if ($antpower) {
            $command = $command . " -p";
        }

        unlink("/tmp/hackrf_log");
        $this->execBackground("$command > /tmp/hackrf_log 2>&1");
    }

    private function doHackRF_Stop()
    {
        exec("killall hackrf_transfer");

        $this->response = array("success" => true);
    }

    private function getHackRF_Log()
    {
        $log = file_get_contents('/tmp/hackrf_log');

        file_put_contents("/root/test", $log);

        $this->response = array("success" => true, "log" => $log);
    }
}

// 01110100 01101000 01100001 01101110 01101011 00100000 01111001 01101111 01110101 00100000 01110011 01100101 01100010 01100001 
// 01110011 01110100 01101001 01100001 01101110 00101110 0001010 01101001 00100000 01101100 01101111 01110110 01100101 00100000
// 01111001 01101111 01110101 00100000 00111100 00110011


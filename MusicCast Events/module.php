<?php

declare(strict_types=1);
	class MusicCastEvents extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			$this->ForceParent('{82347F20-F541-41E1-AC5B-A636FD3AE2D8}');
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
		}

		public function ForwardData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage('Splitter FRWD', utf8_decode($data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort));

			$this->SendDataToParent(json_encode(['DataID' => '{C8792760-65CF-4C53-B5C7-A30FCC84FEFE}', 'Buffer' => $data->Buffer, $data->ClientIP, $data->ClientPort]));

			return 'String data for device instance!';
		}

		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage('Splitter RECV', utf8_decode($data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort));

			$this->SendDataToChildren(json_encode(['DataID' => '{9289561D-252B-265E-D638-3898E391FD06}', 'Buffer' => $data->Buffer, $data->ClientIP, $data->ClientPort]));
		}
	}
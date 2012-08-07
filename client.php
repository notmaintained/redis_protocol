<?php

	namespace sandeepshetty\redis_protocol;

	class SocketException extends \Exception { }
	class RedisProtocolException extends \Exception { }

	const STATUS_REPLY = '+';
	const ERROR_REPLY = '-';
	const INTEGER_REPLY = ':';
	const BULK_REPLY = '$';
	const MULTI_BULK_REPLY = '*';


	function client($host='127.0.0.1', $port=6379, $timeout=NULL)
	{
		$timeout = $timeout ?: ini_get("default_socket_timeout");
		$fp = fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$fp) throw new SocketException($errstr, $errno);

		return function ($cmd) use ($fp)
		{
			if ('quit' == strtolower($cmd)) return fclose($fp);
			$return = fwrite($fp, _multi_bulk_reply($cmd));
			if ($return === FALSE) 	throw new SocketException();
			return _reply($fp);
		};
	}

		function _multi_bulk_reply($cmd)
		{
			$tokens = str_getcsv($cmd, ' ', '"');
			$number_of_arguments = count($tokens);
			$multi_bulk_reply = "*$number_of_arguments\r\n";
			foreach ($tokens as $token) $multi_bulk_reply .= _bulk_reply($token);
			return $multi_bulk_reply;
		}

		function _bulk_reply($arg)
		{
			return '$'.strlen($arg)."\r\n".$arg."\r\n";
		}

		function _reply($fp)
		{
			$reply = fgets($fp);
			if (FALSE === $reply) throw new SocketException('Error Reading Reply');

			$reply = trim($reply);
			$reply_type = $reply[0];
			$data = substr($reply, 1);

			switch($reply[0])
			{
				case STATUS_REPLY:
					if ('ok' == strtolower($data)) return true;
					return $data;

				case ERROR_REPLY:
					throw new RedisProtocolException(substr($data, 4));

				case INTEGER_REPLY:
					return $data;

				case BULK_REPLY:
					$data_length = intval($data);
					if ($data_length < 0) return NULL;
					$bulk_reply = stream_get_contents($fp, $data_length + strlen("\r\n"));
					if (FALSE === $bulk_reply) throw new SocketException('Error Reading Bulk Reply');
					return trim($bulk_reply);

				case MULTI_BULK_REPLY:
					$bulk_reply_count = intval($data);
					if ($bulk_reply_count < 0) return NULL;
					$multi_bulk_reply = array();
					foreach(range(1, $bulk_reply_count) as $i) $multi_bulk_reply[] = _reply();
					return $multi_bulk_reply;

				default:
					throw new RedisProtocolException("Unknown Reply Type: $reply");
			}
		}

?>
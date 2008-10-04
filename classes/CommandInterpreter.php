<?php
/*
 * Laconica - a distributed open-source microblogging tool
 * Copyright (C) 2008, Controlez-Vous, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('LACONICA')) { exit(1); }

class CommandInterpreter {
	
	function handle_command($user, $text) {
		# XXX: localise

		$text = preg_replace('/\s+/', ' ', trim($text));
		list($cmd, $arg) = explode(' ', $text, 2);

		# We try to support all the same commands as Twitter, see
		# http://getsatisfaction.com/twitter/topics/what_are_the_twitter_commands
		# There are a few compatibility commands from earlier versions of 
		# Laconica
		
		switch(strtolower($cmd)) {
		 case 'help':
			if ($arg) {
				return NULL;
			}
			return new HelpCommand($user);
		 case 'on':
			if ($arg) {
				list($other, $extra) = explode(' ', $arg, 2);
				if ($extra) {
					return NULL;
				} else {
					return new OnCommand($user, $other);
				}
			} else {
				return new OnCommand($user);
			}
		 case 'off':
			if ($arg) {
				list($other, $extra) = explode(' ', $arg, 2);
				if ($extra) {
					return NULL;
				} else {
					return new OffCommand($user, $other);
				}
			} else {
				return new OffCommand($user);
			}
		 case 'stop':
		 case 'quit':
			if ($arg) {
				return NULL;
			} else {
				return new OffCommand($user);
			}
		 case 'follow':
		 case 'sub':
			if (!$arg) {
				return NULL;
			}
			list($other, $extra) = explode(' ', $arg, 2);
			if ($extra) {
				return NULL;
			} else {
				return new SubCommand($user, $other);
			}
		 case 'leave':
		 case 'unsub':
			if (!$arg) {
				return NULL;
			}
			list($other, $extra) = explode(' ', $arg, 2);
			if ($extra) {
				return NULL;
			} else {
				return new UnsubCommand($user, $other);
			}
		 case 'get':
		 case 'last':
			if (!$arg) {
				return NULL;
			}
			list($other, $extra) = explode(' ', $arg, 2);
			if ($extra) {
				return NULL;
			} else {
				return new GetCommand($user, $other);
			}
		 case 'd':
			if (!$arg) {
				return NULL;
			}
			list($other, $extra) = explode(' ', $arg, 2);
			if (!$extra) {
				return NULL;
			} else {
				return new MessageCommand($user, $other, $extra);
			}
		 case 'whois':
			if (!$arg) {
				return NULL;
			}
			list($other, $extra) = explode(' ', $arg, 2);
			if ($extra) {
				return NULL;
			} else {
				return new WhoisCommand($user, $other);
			}
		 case 'fav':
			if (!$arg) {
				return NULL;
			}
			list($other, $extra) = explode(' ', $arg, 2);
			if ($extra) {
				return NULL;
			} else {
				return new FavCommand($user, $other);
			}
		 case 'nudge':
			if (!$arg) {
				return NULL;
			}
			list($other, $extra) = explode(' ', $arg, 2);
			if ($extra) {
				return NULL;
			} else {
				return new NudgeCommand($user, $other);
			}
		 case 'stats':
			if ($arg) {
				return NULL;
			}
			return new StatsCommand($user);
		 case 'invite':
			if (!$arg) {
				return NULL;
			}
			list($other, $extra) = explode(' ', $arg, 2);
			if ($extra) {
				return NULL;
			} else {
				return new InviteCommand($user, $other);
			}
		 case 'track':
			if (!$arg) {
				return NULL;
			}
			list($word, $extra) = explode(' ', $arg, 2);
			if ($extra) {
				return NULL;
			} else if ($word == 'off') {
				return new TrackOffCommand($user);
			} else {
				return new TrackCommand($user, $word);
			}
		 case 'untrack':
			if (!$arg) {
				return NULL;
			}
			list($word, $extra) = explode(' ', $arg, 2);
			if ($extra) {
				return NULL;
			} else if ($word == 'all') {
				return new TrackOffCommand($user);
			} else {
				return new UntrackCommand($user, $word);
			}
		 case 'tracks':
		 case 'tracking':
			if ($arg) {
				return NULL;
			}
			return new TrackingCommand($user);
		 default:
			return false;
		}
	}
}

class Channel {
	
	function on($user) {
	}

	function off($user) {
	}

	function output($user) {
	}
	
	function error($user) {
	}
}

class Command {
	
	var $user = NULL;
	
	function __construct($user=NULL) {
		$this->user = $user;
	}
	
	function execute($channel) {
		return false;
	}
}

class UnimplementedCommand extends Command {
	function execute($channel) {
		$channel->error(_("Sorry, this command is not yet implemented."));
	}
}

class TrackingCommand extends UnimplementedCommand {
}

class TrackOffCommand extends UnimplementedCommand {
}

class TrackCommand extends UnimplementedCommand {
	var $word = NULL;
	function __construct($user, $word) {
		parent::__construct($user);
		$this->word = $word;
	}
}

class UntrackCommand extends UnimplementedCommand {
	var $word = NULL;
	function __construct($user, $word) {
		parent::__construct($user);
		$this->word = $word;
	}
}

class NudgeCommand extends UnimplementedCommand {
	var $other = NULL;
	function __construct($user, $other) {
		parent::__construct($user);
		$this->other = $other;
	}
}

class InviteCommand extends UnimplementedCommand {
	var $other = NULL;
	function __construct($user, $other) {
		parent::__construct($user);
		$this->other = $other;
	}
}

class StatsCommand extends UnimplementedCommand {
}

class FaveCommand extends UnimplementedCommand {
	var $other = NULL;
	function __construct($user, $other) {
		parent::__construct($user);
		$this->other = $other;
	}
}

class WhoisCommand extends UnimplementedCommand {
	var $other = NULL;
	function __construct($user, $other) {
		parent::__construct($user);
		$this->other = $other;
	}
}

class MessageCommand extends UnimplementedCommand {
	var $other = NULL;
	var $text = NULL;
	function __construct($user, $other, $text) {
		parent::__construct($user);
		$this->other = $other;
		$this->text = $other;		
	}
}

class GetCommand extends UnimplementedCommand {
	var $other = NULL;
	function __construct($user, $other) {
		parent::__construct($user);
		$this->other = $other;
	}
}

class SubCommand extends UnimplementedCommand {
	var $other = NULL;
	function __construct($user, $other) {
		parent::__construct($user);
		$this->other = $other;
	}
}

class UnsubCommand extends UnimplementedCommand {
	var $other = NULL;
	function __construct($user, $other) {
		parent::__construct($user);
		$this->other = $other;
	}
}

class OffCommand extends UnimplementedCommand {
	var $other = NULL;
	function __construct($user, $other=NULL) {
		parent::__construct($user);
		$this->other = $other;
	}
}

class OnCommand extends UnimplementedCommand {
	var $other = NULL;
	function __construct($user, $other=NULL) {
		parent::__construct($user);
		$this->other = $other;
	}
}

class HelpCommand extends UnimplementedCommand {
}
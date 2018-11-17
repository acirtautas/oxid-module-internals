<?php

namespace OxidCommunity\ModuleInternals\Core;

//used to have a upgrade path from 1.0.1 where this class did exist
class_alias("\OxidCommunity\ModuleInternals\Core\Module_parent","\OxidCommunity\ModuleInternals\Core\InternalModule_parent");
class_alias("\OxidCommunity\ModuleInternals\Core\InternalModule","\OxidCommunity\ModuleInternals\Core\Module");

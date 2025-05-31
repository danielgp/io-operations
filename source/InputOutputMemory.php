<?php

/*
 * Copyright (c) 2018 - 2025 Daniel Popiniuc.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Daniel Popiniuc
 */

namespace danielgp\io_operations;

trait InputOutputMemory
{
    public function getMemoryUsageString()
    {
        return '<span style="color:grey!important;font-weight:bold;">['
            . round((memory_get_usage(true) / 1024 / 1024), 2) . ' MB]</span>';
    }
}

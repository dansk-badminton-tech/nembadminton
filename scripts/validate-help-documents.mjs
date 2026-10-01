import {validateHelpDocuments} from '../resources/js/admin-v2/help/markdown.js'
import {loadHelpDocuments} from './load-help-documents.mjs'

try {
    const documents = await loadHelpDocuments()
    const errors = validateHelpDocuments(documents)

    if (errors.length > 0) {
        throw new Error(errors.join('\n'))
    }

    console.log(`Validated ${documents.length} Help documents.`)
} catch (error) {
    console.error(`Help documentation validation failed:\n${error.message}`)
    process.exitCode = 1
}

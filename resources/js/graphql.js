import ApolloClient from 'apollo-boost'
import VueApollo from 'vue-apollo'
import {getAuthToken, isLoggedIn} from "./auth";

const apolloClient = new ApolloClient(
    {
        uri: '/graphql',
        request: (operation) => {
            if (isLoggedIn()) {
                operation.setContext(
                    {
                        headers: {
                            Authorization: 'Bearer ' + getAuthToken()
                        }
                    })
            }
        }
    }
)

const apolloProvider = new VueApollo({
                                         defaultClient: apolloClient,
                                     }
)
export default apolloProvider
